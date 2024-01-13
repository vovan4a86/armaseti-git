<?php

namespace App\Http\Controllers;

use Doctrine\DBAL\Query\QueryBuilder;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\ParentCatalogFilter;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductChar;
use Fanky\Admin\Settings;
use Fanky\Admin\Text;
use Fanky\Auth\Auth;
use SEOMeta;
use Request;
use View;

class CatalogController extends Controller
{
    public function index()
    {
        $page = Page::getByPath(['catalog']);
        if (!$page) {
            return abort(404);
        }

        $bread = $page->getBread();
        $page->h1 = $page->getH1();
//        $page->setSeo();
//        $page = $this->add_region_seo($page);

        $categories = Catalog::public()->whereParentId(0)->orderBy('order')->get();

        return view(
            'catalog.index',
            [
                'h1' => $page->h1,
                'text' => $page->text,
                'title' => $page->title,
                'bread' => $bread,
                'categories' => $categories,
            ]
        );
    }

    public function view($alias)
    {
        $path = explode('/', $alias);
        /* проверка на продукт в категории */
        $product = null;
        $end = array_pop($path);
        $category = Catalog::getByPath($path);
        if ($category && $category->published) {
            $product = Product::whereAlias($end)
                ->public()
                ->whereCatalogId($category->id)->first();
        }
        if ($product) {
            return $this->product($product);
        } else {
            array_push($path, $end);

            return $this->category($path + [$end]);
        }
    }

    public function category($path)
    {
        $category = Catalog::getByPath($path);
        if (!$category || !$category->published) {
            abort(404, 'Страница не найдена');
        }

        $bread = $category->getBread();
        $category->setSeo();
        $category->ogGenerate();
        $category->generateTitle();
        $category->generateDescription();
        $category = $this->add_region_seo($category);

        if (count(request()->query())) {
            $canonical = $category->url;
        } else {
            $canonical = null;
        }

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.catalog.catalogEdit', [$category->id]));
        }

        $view = 'catalog.category';

        if (count($category->children)) {
            $cat_children_ids = $category->getRecurseChildrenIds();
            $cat_children_ids[] = $category->id;

            $products = Product::whereIn('catalog_id', $cat_children_ids)
                ->public()
                ->paginate(Settings::get('products_per_page', 9));
        } else {
            $cat_children_ids[] = $category->id;
            $products = $category->public_products;
        }

//        $all_filters = $category->getRecurseFilterList();
        $root_category = $category->findRootCategory();
        $all_filters = ParentCatalogFilter::where('catalog_id', $root_category->id)
            ->public()
            ->orderBy('order')
            ->get();

        $filters_list = [];
        foreach ($all_filters as $filter) {
//            if ($filter->published) {
            $values = ProductChar::where('name', $filter->name)
                ->whereIn('catalog_id', $cat_children_ids)
                ->select('value')
                ->distinct()
                ->pluck('value')
                ->all();
            natsort($values);
            $filters_list[$filter->name] = [
                'translit' => Text::translit($filter->name),
                'values' => $values
            ];
//            }
        }

        if (request()->ajax()) {
//            $data = request()->all();
//
//            $found_ids_query = ProductChar::whereIn('catalog_id', $cat_children_ids);
//
//            $found_ids = $found_ids_query->select('product_id')->distinct()->pluck('product_id')->all();
//            foreach ($data as $name => $values) {
//                $found_ids_query = ProductChar::whereIn('product_id', $found_ids)
//                    ->whereIn('value', $values);
//                $found_ids = $found_ids_query->select('product_id')->distinct()->pluck('product_id')->all();
//            }
//            $products = Product::whereIn('id', $found_ids)->get('name');
//            $items = [];
//            foreach ($products as $product) {
//                $items[] = view('catalog.product_item', ['product' => $product])->render();
//            }
//
//            return ['success' => true, 'items' => $items];

            $view_items = [];
            foreach ($products as $item) {
                //добавляем новые элементы
                $view_items[] = view(
                    'catalog.product_item',
                    [
                        'product' => $item,
                    ]
                )->render();
            }

            $btn_paginate = null;
            if ($products->nextPageUrl()) {
                $btn_paginate = view('paginations.load_more', ['paginator' => $products])->render();
            }

            $paginate = view('paginations.with_pages', ['paginator' => $products])->render();

            return [
                'items' => $view_items,
                'btn' => $btn_paginate,
                'paginate' => $paginate
            ];
        }

        $data = [
            'bread' => $bread,
            'category' => $category,
            'canonical' => $canonical,
            'h1' => $category->getH1(),
            'text' => $category->text,
            'children' => $category->public_children,
            'products' => $products,
            'filters_list' => $filters_list,
        ];

        return view($view, $data);
    }

    public function product(Product $product)
    {
        $category = Catalog::find($product->catalog_id);
        $bread = $product->getBread();
        $product->generateTitle();
        $product->generateDescription();
        if (!$product->text) {
            $product->generateText();
        }
        $product->setSeo();
        $product->ogGenerate();
        $product = $this->add_region_seo($product);

        $images = $product->images;

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.catalog.productEdit', [$product->id]));
        }

        $related = Product::query()
            ->where('id', '!=', $product->id)
            ->limit(Settings::get('related_per_page', 5))
            ->inRandomOrder()
            ->get();

        return view(
            'catalog.product',
            [
                'product' => $product,
                'h1' => $product->getH1(),
                'bread' => $bread,
                'images' => $images,
                'name' => $product->name,
                'text' => $product->text,
                'related' => $related,
                'top_view' => $category->getCatalogTopView()
            ]
        );
    }

    public function search()
    {
        $see = Request::get('see', 'all');
        $products_inst = Product::query();
        if ($s = Request::get('search')) {
            $products_inst->where(
                function ($query) use ($s) {
                    /** @var QueryBuilder $query */
                    //сначала ищем точное совпадение с началом названия товара
                    return $query->orWhere('name', 'LIKE', $s . '%');
                }
            );

            if (Request::ajax()) {
                //если нашлось больше 10 товаров, показываем их
                if ($products_inst->count() >= 10) {
                    $products = $products_inst->limit(10)->get()->transform(
                        function ($item) {
                            return [
                                'name' => $item->name . ' [' . $item->article . ']',
                                'url' => $item->url
                            ];
                        }
                    );
                } else {
                    //если меньше 10, разницу дополняем с совпадением по всему названию товара и артиклу
                    $count_before = $products_inst->count();
                    $sub = 10 - $count_before;
                    $adds_query = Product::query()
                        ->orWhere('name', 'LIKE', '%' . str_replace(' ', '%', $s) . '%')
                        ->orWhere('article', 'LIKE', '%' . str_replace(' ', '%', $s) . '%');
                    $adds_prod = $adds_query->limit($sub)->get();
                    $prods_before = $products_inst->limit($count_before)->get();
                    $all_prods = $prods_before->merge($adds_prod);
                    $products = $all_prods->transform(
                        function ($item) {
                            return [
                                'name' => $item->name . ' [' . $item->article . ']',
                                'url' => $item->url
                            ];
                        }
                    );
                }
                return ['data' => $products];
            }

            if ($see == 'all' || !is_numeric($see)) {
                $products = $products_inst->paginate(Settings::get('search_per_page'));
            } else {
                $products = $products_inst->paginate($see);
                $filter_query = Request::only(['see', 'price', 'in_stock']);
                $filter_query = array_filter($filter_query);
                $products->appends($filter_query);
            }
        } else {
            $products = collect();
        }


        return view(
            'search.index',
            [
                'items' => $products,
                'h1' => 'Результат поиска «' . $s . '»',
                'title' => 'Результат поиска «' . $s . '»',
                'query' => $see,
                'name' => 'Поиск ' . $s,
                'keywords' => 'Поиск',
                'description' => 'Поиск',
            ]
        );
    }

    public function compare()
    {
        $items_ids = \Session::get('compare');

        $items = Product::whereIn('id', $items_ids)->with(['chars'])->get();

        $compare_names = ProductChar::whereIn('product_id', $items_ids)->groupBy('name')->get();
//        dd($compare_names);

        $get_diffs = request()->get('diff');

        if ($get_diffs) {
            $diffs = [];
            foreach ($compare_names as $char) {
                $compare_val = null;
                foreach ($items as $i => $item) {
                    if ($i == 0) {
                        $compare_val = $item->getCharByName($char->name);
                    } else {
                        if ($compare_val != $item->getCharByName($char->name)) {
                            $diffs[] = $char;
                            break;
                        }
                    }
                }
            }
        }

        return view(
            'catalog.compare',
            [
                'compare_names' => $get_diffs ? $diffs : $compare_names,
                'items' => $items
            ]
        );
    }

}
