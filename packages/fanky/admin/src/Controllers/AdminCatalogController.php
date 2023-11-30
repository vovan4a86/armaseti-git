<?php

namespace Fanky\Admin\Controllers;

use Exception;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\ProductChar;
use Fanky\Admin\Models\ProductDoc;
use Fanky\Admin\Pagination;
use Request;
use Settings;
use Validator;
use Text;
use DB;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductImage;

class AdminCatalogController extends AdminController
{

    public function getIndex()
    {
        $catalogs = Catalog::orderBy('order')->get();

        return view(
            'admin::catalog.main',
            [
                'catalogs' => $catalogs
            ]
        );
    }

    public function getGetCatalogs($id = 0): array
    {
        $catalogs = Catalog::whereParentId($id)->orderBy('order')->get();
        $result = [];
        foreach ($catalogs as $catalog) {
            $has_children = (bool)$catalog->children()->count();
            $result[] = [
                'id' => $catalog->id,
                'text' => $catalog->name,
                'children' => $has_children,
                'icon' => ($catalog->published) ? 'fa fa-eye text-green' : 'fa fa-eye-slash text-muted',
            ];
        }

        return $result;
    }

    public function postProducts($catalog_id)
    {
        $catalog = Catalog::findOrFail($catalog_id);
        $products = Pagination::init($catalog->products()->orderBy('order'), 20)->get();

        return view(
            'admin::catalog.products',
            [
                'catalog' => $catalog,
                'products' => $products
            ]
        );
    }

    public function getProducts($catalog_id)
    {
        $catalogs = Catalog::orderBy('order')->get();

        return view(
            'admin::catalog.main',
            [
                'catalogs' => $catalogs,
                'content' => $this->postProducts($catalog_id)
            ]
        );
    }

    public function postCatalogEdit($id = null)
    {
        /** @var Catalog $catalog */
        if (!$id || !($catalog = Catalog::findOrFail($id))) {
            $catalog = new Catalog(
                [
                    'parent_id' => Request::get('parent'),
                    'published' => 1
                ]
            );
        }
        $catalogs = Catalog::orderBy('order')
            ->where('id', '!=', $catalog->id)
            ->get();

        $catalogProducts = $catalog->getRecurseProducts()
            ->orderBy('name')
            ->pluck('id', 'name')
            ->all();

        $catalogFiltersList = $catalog->parent_id == 0 ? $catalog->getRecurseFilterList() : [];

//        $show_catalog_filters = [];
//        foreach ($catalogFiltersList as $name) {
//            $show_catalog_filters[$name] = $catalog->product_chars()
//                ->where('name', $name)
//                ->select('value')
//                ->distinct()
//                ->pluck('value')
//                ->all();
//        }
//        dd($catalog->getRecurseFilterList());
//
        return view(
            'admin::catalog.catalog_edit',
            [
                'catalog' => $catalog,
                'catalogs' => $catalogs,
                'catalogProducts' => $catalogProducts,
                'catalogFiltersList' => $catalogFiltersList
            ]
        );
    }

    public function getCatalogEdit($id = null)
    {
        $catalogs = Catalog::orderBy('order')->get();

        return view(
            'admin::catalog.main',
            [
                'catalogs' => $catalogs,
                'content' => $this->postCatalogEdit($id)
            ]
        );
    }

    public function postCatalogSave(): array
    {
        $id = Request::input('id');
        $data = Request::except(['id', 'filters']);
//        $filters = Request::get('filters');

        if (!array_get($data, 'alias')) {
            $data['alias'] = Text::translit($data['name']);
        }
        if (!array_get($data, 'title')) {
            $data['title'] = $data['name'];
        }
        if (!array_get($data, 'h1')) {
            $data['h1'] = $data['name'];
        }
        if (!array_get($data, 'published')) {
            $data['published'] = 0;
        }

        $image = Request::file('image');

        // валидация данных
        $validator = Validator::make(
            $data,['name' => 'required']
        );
        if ($validator->fails()) {
            return ['errors' => $validator->messages()];
        }

        $catalog = Catalog::find($id);

        // Загружаем изображение
        if ($image) {
            $file_name = Catalog::uploadImage($image);
            $data['image'] = $file_name;
        }

        $redirect = false;
        // сохраняем страницу

        if (!$catalog) {
            $data['order'] = Catalog::where('parent_id', $data['parent_id'])->max('order') + 1;
            $catalog = Catalog::create($data);
            $redirect = true;
        } else {
            $catalog->update($data);
        }

        //сохраняем фильтры раздела
//        foreach ($catalog->filters_list as $filter) {
//            if (in_array($filter->id, $filters)) {
//                $filter->update(['published' => 1]);
//            } else {
//                $filter->update(['published' => 0]);
//            }
//        }

        if ($redirect) {
            return ['redirect' => route('admin.catalog.catalogEdit', [$catalog->id])];
        } else {
            return ['success' => true, 'msg' => 'Изменения сохранены'];
        }
    }

    public function postCatalogReorder(): array
    {
        // изменение родителя
        $id = Request::input('id');
        $parent = Request::input('parent');
        DB::table('catalogs')->where('id', $id)->update(array('parent_id' => $parent));
        // сортировка
        $sorted = Request::input('sorted', []);
        foreach ($sorted as $order => $id) {
            DB::table('catalogs')->where('id', $id)->update(array('order' => $order));
        }

        return ['success' => true];
    }

    public function postTopViewDel($id): array
    {
        $catalog = Catalog::find($id);
        if(!$catalog) return ['errors' => 'catalog not found'];

        $catalog->deleteTopView();
        $catalog->update(['top_view' => null]);

        return ['success' => true];
    }


    /**
     * @throws Exception
     */
    public function postCatalogDelete($id): array
    {
        $catalog = Catalog::findOrFail($id);
        $catalog->delete();

        return ['success' => true];
    }

    public function postCatalogImageDelete($id): array
    {
        $catalog = Catalog::find($id);
        if(!$catalog) return ['errors' => 'catalog_not_found'];

        $catalog->deleteImage();
        $catalog->update(['image' => null]);

        return ['success' => true];
    }

    public function postProductEdit($id = null)
    {
        /** @var Product $product */
        if (!$id || !($product = Product::findOrFail($id))) {
            $product = new Product();
            $product->catalog_id = Request::get('catalog');
            $product->order = Product::whereCatalogId(Request::get('catalog'))->max('order') + 1;
            $product->published = 1;
        }
        $catalogs = Catalog::getCatalogList();
        $tab = request()->get('tab');

        $data = [
            'product' => $product,
            'catalogs' => $catalogs,
            'tab' => $tab
        ];
        return view('admin::catalog.product_edit', $data);
    }

    public function getProductEdit($id = null)
    {
        $catalogs = Catalog::orderBy('order')->get();

        return view(
            'admin::catalog.main',
            [
                'catalogs' => $catalogs,
                'content' => $this->postProductEdit($id)
            ]
        );
    }

    public function postProductSave(): array
    {
        $id = Request::get('id');
        $data = Request::except(['id']);

        if (!array_get($data, 'published')) {
            $data['published'] = 0;
        }
        if (!array_get($data, 'alias')) {
            $data['alias'] = Text::translit($data['name']);
        }
        if (!array_get($data, 'title')) {
            $data['title'] = $data['name'];
        }
        if (!array_get($data, 'h1')) {
            $data['h1'] = $data['name'];
        }

        $rules = [
            'name' => 'required'
        ];

        $rules['alias'] = $id
            ? 'required|unique:products,alias,' . $id . ',id,catalog_id,' . $data['catalog_id']
            : 'required|unique:products,alias,null,id,catalog_id,' . $data['catalog_id'];
        // валидация данных
        $validator = Validator::make(
            $data,
            $rules
        );
        if ($validator->fails()) {
            return ['errors' => $validator->messages()];
        }

        $redirect = false;

        $data['price'] = str_replace(',', '.', $data['price']);

        // сохраняем страницу
        $product = Product::find($id);

        //сохраняем Параметры
        $chars_data = Request::get('chars', []);
        $char_ids = array_get($chars_data, 'id', []);
        $char_names = array_get($chars_data, 'name', []);
        $char_values = array_get($chars_data, 'value', []);
        $chars_list = [];
        foreach ($char_ids as $key => $char_id) {
            $chars_list[] = [
                'id'	=> $char_id,
                'catalog_id' => $product->catalog_id,
                'product_id' => $product->id,
                'name'	=> array_get($char_names, $key),
                'translit' => Text::translit(array_get($char_names, $key)),
                'value'	=> array_get($char_values, $key),
            ];
        }
        array_pop($chars_list);

        if (!$product) {
            $data['order'] = Product::where('catalog_id', $data['catalog_id'])->max('order') + 1;
            $product = Product::create($data);
            $redirect = true;
        } else {
            $product->update($data);
        }

        foreach ($chars_list as $key => $char) {
            $p = ProductChar::findOrNew(array_get($char, 'id'));
            if(!$p->id) $redirect = false;
            $char['product_id'] = $product->id;
            $char['order'] = $key;
            $p->fill($char)->save();
        }

        return $redirect
            ? ['redirect' => route('admin.catalog.productEdit', [$product->id])]
            : ['success' => true, 'msg' => 'Изменения сохранены'];
    }

    public function postProductReorder(): array
    {
        $sorted = Request::input('sorted', []);
        foreach ($sorted as $order => $id) {
            DB::table('products')->where('id', $id)->update(array('order' => $order));
        }

        return ['success' => true];
    }

    public function postUpdateOrder($id): array
    {
        $order = Request::get('order');
        Product::whereId($id)->update(['order' => $order]);

        return ['success' => true];
    }

    public function postProductDelete($id): array
    {
        $product = Product::findOrFail($id);
        foreach ($product->images as $item) {
            $item->deleteImage();
            $item->delete();
        }
        $product->delete();

        return ['success' => true];
    }

    public function postProductImageUpload($product_id): array
    {
        $product = Product::findOrFail($product_id);
        $images = Request::file('images');
        $items = [];
        if ($images) {
            foreach ($images as $image) {
                $file_name = ProductImage::uploadImage($image);
                $order = ProductImage::where('product_id', $product_id)->max('order') + 1;
                $item = ProductImage::create(['product_id' => $product_id, 'image' => $file_name, 'order' => $order]);
                $items[] = $item;
            }
        }

        $html = '';
        foreach ($items as $item) {
            $html .= view('admin::catalog.product_image', ['image' => $item, 'active' => '']);
        }

        return ['html' => $html];
    }

    public function postProductImageOrder(): array
    {
        $sorted = Request::get('sorted', []);
        foreach ($sorted as $order => $id) {
            ProductImage::whereId($id)->update(['order' => $order]);
        }

        return ['success' => true];
    }

    public function postProductImageDelete($id): array
    {
        /** @var ProductImage $item */
        $item = ProductImage::findOrFail($id);
        $item->deleteImage();
        $item->delete();

        return ['success' => true];
    }

    public function postProductDeleteChar($id) {
        $char = ProductChar::find($id);
        if (!$char) return ['success' => false, 'msg' => 'Характеристика не найдена'];

        $char->delete();
        return ['success' => true, 'msg' => 'Характеристика удалена'];
    }

    //документы
    public function postProductAddDoc($product_id): array {
        $docs = Request::file('docs');
        $items = [];
        if ($docs) foreach ($docs as $doc) {
            $file_name = ProductDoc::uploadFile($doc);
            $order = ProductDoc::where('product_id', $product_id)
                    ->max('order') + 1;
            $item = ProductDoc::create([
                                           'product_id' => $product_id,
                                           'name' => 'Документ ' . $order,
                                           'file' => $file_name,
                                           'order' => $order
                                       ]);
            $items[] = $item;
        }

        $html = '';
        foreach ($items as $item) {
            $html .= view('admin::catalog.tabs.doc_row', ['doc' => $item]);
        }

        return ['html' => $html];
    }

    public function postProductEditDoc($id) {
        $doc = ProductDoc::findOrFail($id);
        return view('admin::catalog.tabs.doc_edit', ['doc' => $doc]);
    }

    public function postProductSaveDoc($id) {
        $doc = ProductDoc::findOrFail($id);
        $data = Request::only('name');
        $doc->name = $data['name'];
        $doc->save();

        return [
            'success' => true,
            'redirect' => route('admin.catalog.productEdit', ['id' => $doc->product_id, 'tab' => 'docs'])];
    }

    public function postProductDelDoc($id): array {
        $item = ProductDoc::findOrFail($id);
        $item->deleteSrcFile();
        $item->delete();

        return ['success' => true];
    }

    public function postProductUpdateOrderDoc(){
        $sorted = Request::input('sorted', []);
        foreach ($sorted as $order => $id) {
            DB::table('product_docs')->where('id', $id)->update(array('order' => $order));
        }

        return ['success' => true];
    }

}
