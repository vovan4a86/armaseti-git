<?php

namespace Fanky\Admin\Controllers;

use Exception;
use Fanky\Admin\Models\CatalogDoc;
use Fanky\Admin\Models\CatalogFilter;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\ParentCatalogFilter;
use Fanky\Admin\Models\ProductChar;
use Fanky\Admin\Models\ProductDoc;
use Fanky\Admin\Pagination;
use Fanky\Auth\Auth;
use Request;
use Settings;
use Symfony\Component\Debug\Debug;
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
        $per_page = Request::get('per_page');
        if (!$per_page) {
            $per_page = session('per_page', 30);
        }
        $catalog = Catalog::findOrFail($catalog_id);

        $products = $catalog->products()->orderBy('order');
//        $products = Pagination::init($catalog->products()->orderBy('order'), 20)->get();

        if ($q = Request::get('q')) {
            $products->where(function ($query) use ($q) {
                $query->orWhere('name', 'LIKE', '%' . $q . '%')
                    ->orWhere('article', 'LIKE', '%' . $q . '%');
            });
        }
        $products = Pagination::init($products, $per_page)->get();
        $catalog_list = Catalog::getCatalogList();
        session(['per_page' => $per_page]);

        return view(
            'admin::catalog.products',
            [
                'catalog' => $catalog,
                'products' => $products,
                'catalog_list' => $catalog_list
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

        $tab = Request::get('tab');
        $catalogFiltersList = $catalog->getRecurseFilterList();

        return view(
            'admin::catalog.catalog_edit',
            [
                'catalog' => $catalog,
                'catalogs' => $catalogs,
                'tab' => $tab,
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
        if (!Auth::user()->isAdmin) {
            return ['alert' => 'Не хватает прав на действие!'];
        }

        $id = Request::input('id');
        $data = Request::except(['id', 'filters']);

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
        $icon = Request::file('icon');
        $filters = Request::get('filters');

        // валидация данных
        $validator = Validator::make(
            $data,
            ['name' => 'required']
        );
        if ($validator->fails()) {
            return ['errors' => $validator->messages()];
        }

        $catalog = Catalog::find($id);
        $redirect = false;


        // Загружаем изображение
        if ($image) {
            $file_name = Catalog::uploadImage($image);
            $data['image'] = $file_name;
            $redirect = true;
        }
        // Загружаем иконку
        if ($icon) {
            $file_name = Catalog::uploadIcon($icon);
            $data['menu_icon'] = $file_name;
            $redirect = true;
        }

        // сохраняем страницу
        if (!$catalog) {
            $data['order'] = Catalog::where('parent_id', $data['parent_id'])->max('order') + 1;
            $catalog = Catalog::create($data);
            $redirect = true;
        } else {
            $catalog->update($data);

            //обновление фильтров для каталога без родителей
            if (!count($catalog->children)) {
                $catalog_filters = $catalog->filters_list()->pluck('id');
                CatalogFilter::whereIn('id', $catalog_filters)->update(['published' => 1]);
                foreach ($catalog_filters as $filter_id) {
                    if (!in_array($filter_id, $filters)) {
                        CatalogFilter::whereId($filter_id)->update(['published' => 0]);
                    }
                }
            } else {
                //обновляем значение и у всех вложенных каталогов
                $children_ids = $catalog->getRecurseChildrenIds();
                foreach ($filters as $value) {
                    [$id, $published] = explode(',', $value);
                    $catalog_filter = CatalogFilter::select('name')->find($id);
                    CatalogFilter::whereIn('catalog_id', $children_ids)
                        ->where('name', $catalog_filter->name)
                        ->update(['published' => $published]);
                }
            }
        }

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

    public function postCatalogFilterEdit($id) {
        $filter_id = Request::get('filter_id');
        $catalog_filter = CatalogFilter::findOrFail($filter_id);
        return view('admin::catalog.catalog_filter_edit', [
            'item' => $catalog_filter,
            'catalog_id' => $id
        ]);
    }

    public function postCatalogFilterSaveData($id): array
    {
        $catalog = Catalog::find($id);
        if (count($catalog->children)) {
            $children_ids = $catalog->getRecurseChildrenIds();
        } else {
            $children_ids = $catalog->id;
        }
        $name = Request::get('name');
        $filter_id = Request::get('id');
        $current_filter = CatalogFilter::select('name')->find($filter_id);
        $old_name = $current_filter->name;

        CatalogFilter::whereIn('catalog_id', $children_ids)
            ->where('name', $old_name)
            ->update(['name' => $name]);
        ProductChar::whereIn('catalog_id', $children_ids)
            ->where('name', $old_name)
            ->update(['name' => $name]);


        return [
            'success' => true,
            'redirect' => route('admin.catalog.catalogEdit', [
                'id' => $id,
                'tab' => 'filters'
            ])
        ];
    }

    public function postCatalogFilterDelete($id): array
    {
        $catalog = Catalog::find($id);
        if (count($catalog->children)) {
            $children_ids = $catalog->getRecurseChildrenIds();
        } else {
            $children_ids = $catalog->id;
        }
        $filter_id = Request::get('filter_id');
        $current_filter = CatalogFilter::select('name')->find($filter_id);
        $old_name = $current_filter->name;

        CatalogFilter::whereIn('catalog_id', $children_ids)
            ->where('name', $old_name)
            ->delete();
        ProductChar::whereIn('catalog_id', $children_ids)
            ->where('name', $old_name)
            ->delete();

        return [
            'success' => true,
            'redirect' => route('admin.catalog.catalogEdit', [
                'id' => $id,
                'tab' => 'filters'
            ])
        ];
    }


    /**
     * @throws Exception
     */
    public function postCatalogDelete($id): array
    {
        if (!Auth::user()->isAdmin) {
            return ['alert' => 'Не хватает прав на действие!'];
        }

        $catalog = Catalog::findOrFail($id);
        $catalog->delete();

        return ['success' => true];
    }

    public function postCatalogImageDelete($id): array
    {
        $catalog = Catalog::find($id);
        if (!$catalog) {
            return ['errors' => 'catalog_not_found'];
        }

        $catalog->deleteImage();
        $catalog->update(['image' => null]);

        return ['success' => true];
    }

    public function postCatalogIconDelete($id): array
    {
        $catalog = Catalog::find($id);
        if (!$catalog) {
            return ['errors' => 'catalog_not_found'];
        }

        $catalog->deleteIcon();
        $catalog->update(['menu_icon' => null]);

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
        if (!Auth::user()->isAdmin) {
            return ['alert' => 'Не хватает прав на действие!'];
        }

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
        if (!array_get($data, 'is_new')) {
            $data['is_new'] = 0;
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
                'id' => $char_id,
                'catalog_id' => $product->catalog_id,
                'product_id' => $product->id,
                'order' => $key,
                'name' => array_get($char_names, $key),
                'translit' => Text::translit(array_get($char_names, $key)),
                'value' => array_get($char_values, $key),
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
            if (!$p->id) {
                $redirect = false;
                $c = CatalogFilter::where('catalog_id', $product->catalog_id)
                    ->where('name', array_get($char, 'id'))->first();
                if (!$c) {
                    CatalogFilter::create(
                        [
                            'catalog_id' => $product->catalog_id,
                            'name' => array_get($char, 'name'),
                            'published' => 1,
                            'order' => CatalogFilter::where('catalog_id', $product->catalog_id)->max('order') + 1
                        ]
                    );
                }
            }
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
        if (!Auth::user()->isAdmin) {
            return ['alert' => 'Не хватает прав на действие!'];
        }

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
        $item->deleteImage(null, $item->product->catalog->alias);
        $item->delete();

        return ['success' => true];
    }

    public function postProductDeleteChar($id): array
    {
        $char = ProductChar::find($id);
        if (!$char) {
            return ['success' => false, 'msg' => 'Характеристика не найдена'];
        }

        $hasLastCharName = ProductChar::where('name', $char->name)->count() == 1;

        if ($hasLastCharName) {
            $c_filter = CatalogFilter::where('name', $char->name)
                ->where('catalog_id', $char->catalog_id)->first();
            $c_filter->delete();
        }
        $char->delete();

        return ['success' => true, 'msg' => 'Характеристика удалена'];
    }

    public function postProductUpdateOrderChar(): array
    {
        $sorted = Request::input('sorted', []);
        foreach ($sorted as $order => $id) {
            DB::table('product_chars')->where('id', $id)->update(array('order' => $order));
        }

        return ['success' => true];
    }

    public function postCatalogFilterUpdateOrder(): array
    {
        $sorted = Request::input('sorted', []);
        $catalog_id = Request::get('catalog_id');
        \Debugbar::log($catalog_id);
        foreach ($sorted as $order => $id) {
            DB::table('catalog_filters')
                ->where('catalog_id', $catalog_id)
                ->where('id', $id)
                ->update(array('order' => $order));
        }

        return ['success' => true];
    }

    //mass
    public function postMoveProducts()
    {
        $catalog_id = Request::get('catalog_id');
        $item_ids = Request::get('items', []);
        if ($item_ids && $catalog_id) {
            Product::whereIn('id', $item_ids)
                ->update(['catalog_id' => $catalog_id]);
        }

        return ['success' => true];
    }

    public function postDeleteProducts()
    {
        $item_ids = Request::get('items', []);
        if ($item_ids) {
            $products = Product::whereIn('id', $item_ids)->get();
            foreach ($products as $product) {
                $product->additional_catalogs()->detach();
                $product->delete();
            }
        }

        return ['success' => true];
    }

    public function postDeleteProductsImage()
    {
        $item_ids = Request::get('items', []);
        if ($item_ids) {
            $products = Product::whereIn('id', $item_ids)->get();
            foreach ($products as $product) {
                $images = $product->images;

                if ($images) {
                    foreach ($images as $image) {
                        $image->deleteImage();
                        $image->delete();
                    }
                }
            }
        }

        return ['success' => true];
    }

    public function search()
    {
        $q = Request::get('q');
        \Debugbar::log($q);
        if (!$q) {
            $products = [];
        } else {
            $products = Product::query()->where(function ($query) use ($q) {
                $query->orWhere('name', 'LIKE', '%' . $q . '%');
                $query->orWhere('article', 'LIKE', '%' . $q . '%');
            })->with('catalog')->paginate(30)->appends(['q' => $q]);
        }
        $catalogs = Catalog::orderBy('order')->get();
        $catalog_list = Catalog::getCatalogList();
        $content = view(
            'admin::catalog.search',
            compact('catalogs', 'catalog_list', 'products')
        )->render();
        return view(
            'admin::catalog.main',
            compact('content', 'catalogs')
        );
    }

    //toggle products checkbox
    public function postProductToggleIsNew($id): array
    {
        $product = Product::find($id);
        if (!$product) {
            return ['success' => false];
        }

        if ($product->is_new) {
            $product->update(['is_new' => 0]);
        } else {
            $product->update(['is_new' => 1]);
        }

        return ['success' => true, 'active' => $product->is_new];
    }

    public function postProductToggleIsHit($id): array
    {
        $product = Product::find($id);
        if (!$product) {
            return ['success' => false];
        }

        if ($product->is_hit) {
            $product->update(['is_hit' => 0]);
        } else {
            $product->update(['is_hit' => 1]);
        }

        return ['success' => true, 'active' => $product->is_hit];
    }

    //документы каталога
    public function postCatalogAddDoc($catalog_id): array
    {
        $docs = Request::file('docs');
        $items = [];
        if ($docs) {
            foreach ($docs as $doc) {
                $file_name = CatalogDoc::uploadFile($doc);
                $order = CatalogDoc::where('catalog_id', $catalog_id)
                        ->max('order') + 1;
                $item = CatalogDoc::create(
                    [
                        'catalog_id' => $catalog_id,
                        'name' => 'Документ ' . $order,
                        'file' => $file_name,
                        'order' => $order
                    ]
                );
                $items[] = $item;
            }
        }

        $html = '';
        foreach ($items as $item) {
            $html .= view('admin::catalog.tabs_catalog.doc_row', ['doc' => $item]);
        }

        return ['html' => $html];
    }

    public function postCatalogEditDoc($id)
    {
        $doc = CatalogDoc::findOrFail($id);
        return view('admin::catalog.tabs_catalog.doc_edit', ['doc' => $doc]);
    }

    public function postCatalogSaveDoc($id)
    {
        $doc = CatalogDoc::findOrFail($id);
        $data = Request::only('name');
        $doc->name = $data['name'];
        $doc->save();

        return [
            'success' => true,
            'redirect' => route('admin.catalog.catalogEdit', ['id' => $doc->catalog_id, 'tab' => 'docs'])
        ];
    }

    public function postCatalogDelDoc($id): array
    {
        $item = CatalogDoc::findOrFail($id);
        $item->deleteSrcFile($item->catalog->alias);
        $item->delete();

        return ['success' => true];
    }

    public function postCatalogUpdateOrderDoc()
    {
        $sorted = Request::input('sorted', []);
        foreach ($sorted as $order => $id) {
            DB::table('catalog_docs')->where('id', $id)->update(array('order' => $order));
        }

        return ['success' => true];
    }


    //документы товара
    public function postProductAddDoc($product_id): array
    {
        $docs = Request::file('docs');
        $items = [];
        if ($docs) {
            foreach ($docs as $doc) {
                $file_name = ProductDoc::uploadFile($doc);
                $order = ProductDoc::where('product_id', $product_id)
                        ->max('order') + 1;
                $item = ProductDoc::create(
                    [
                        'product_id' => $product_id,
                        'name' => 'Документ ' . $order,
                        'file' => $file_name,
                        'order' => $order
                    ]
                );
                $items[] = $item;
            }
        }

        $html = '';
        foreach ($items as $item) {
            $html .= view('admin::catalog.tabs.doc_row', ['doc' => $item]);
        }

        return ['html' => $html];
    }

    public function postProductEditDoc($id)
    {
        $doc = ProductDoc::findOrFail($id);
        return view('admin::catalog.tabs.doc_edit', ['doc' => $doc]);
    }

    public function postProductSaveDoc($id)
    {
        $doc = ProductDoc::findOrFail($id);
        $data = Request::only('name');
        $doc->name = $data['name'];
        $doc->save();

        return [
            'success' => true,
            'redirect' => route('admin.catalog.productEdit', ['id' => $doc->product_id, 'tab' => 'docs'])
        ];
    }

    public function postProductDelDoc($id): array
    {
        $item = ProductDoc::findOrFail($id);
        $item->deleteSrcFile($item->catalog->alias);
        $item->delete();

        return ['success' => true];
    }

    public function postProductUpdateOrderDoc()
    {
        $sorted = Request::input('sorted', []);
        foreach ($sorted as $order => $id) {
            DB::table('product_docs')->where('id', $id)->update(array('order' => $order));
        }

        return ['success' => true];
    }

}
