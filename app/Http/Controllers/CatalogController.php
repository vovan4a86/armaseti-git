<?php

namespace App\Http\Controllers;

use Doctrine\DBAL\Query\QueryBuilder;
use Fanky\Admin\Cart;
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
        }

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.catalog.catalogEdit', [$category->id]));
        }

        $children_ids = [];
        if (count($category->children)) {
            $children_ids = $category->getRecurseChildrenIds();
        }
        if (!in_array($category->id, $children_ids)) {
            $children_ids[] = $category->id;
        }

//        $arr = [
//            'diametr-uslovniy-dudn' => ['15 мм'],
//            'material' => ['сталь 20', '123']
//        ];
//
//        $query = '?price_from=' . 0 . '&price_to=' . 12000 . '&in_stock=' . 1;
//        $appends = ['price_from' => 0, 'price_to' => 12000, 'in_stock' => 1];
//        $res = [];
//        foreach ($arr as $name => $values) {
//            foreach ($values as $val) {
//                $res[] = ['value', $val];
//                $query .= '&' . $name . '[]=' . $val;
//                if (count($values) == 1) {
//                    $appends[$name] = $val;
//                } else {
//                    $appends[$name][] = $val;
//                }
//            }
//        }
//        dd($appends);
//        $products = Product::with('chars')->whereIn('catalog_id', $children_ids)
//            ->where('in_stock', 1)
//            ->where('price', '>', 0)
//            ->where('price', '<=', 63000)
//            ->whereHas('chars', function ($query) use ($res) {
//                $query->orWhere($res);
//            })->get();
//

        //макс цена для фильтра
        $filter_max_price = $category->getProductMaxPriceInCatalog();

        //параметры строки
        $data_filter = request()->except(['price_from', 'price_to', 'in_stock']); //фильтры товаров
        $price_from = request()->get('price_from'); //встроенный фильтр
        $price_to = request()->get('price_to'); //встроенный фильтр
        $in_stock = request()->get('in_stock'); //встроенный фильтр

        $appends = ['price_from' => $price_from, 'price_to' => $price_to, 'in_stock' => $in_stock];

        //если фильтровали по встроенным фильтрам (цена/наличие)
        if ($price_from || $price_to || $in_stock) {
            $products_query = Product::whereIn('catalog_id', $children_ids)
                ->where('in_stock', $in_stock)
                ->where('price', '>', $price_from)
                ->where('price', '<=', $price_to);

            //фильтры товаров, кроме цены и наличия
            if (!count($data_filter)) {
                $products = $products_query
                    ->paginate(Settings::get('products_per_page', 9))
                    ->appends($appends);
            } else {
                //формирование массива для запроса + appends
                $result_filters = [];
                foreach ($data_filter as $name => $values) {
                    foreach ($values as $val) {
                        $result_filters[] = ['value', $val];
                        if (count($values) == 1) {
                            $appends[$name] = $val;
                        } else {
                            $appends[$name][] = $val;
                        }
                    }
                }

                //фильтруем по характеристикам товара
                $products = $products_query
                    ->whereHas('chars', function ($query) use ($result_filters) {
                        $query->orWhere($result_filters);
                    })
                    ->paginate(Settings::get('products_per_page', 9))
                    ->appends($appends);
            }
        } else {
            //чистая загрузка страницы без фильтрации
            $products = Product::whereIn('catalog_id', $children_ids)
                ->public()
                ->paginate(Settings::get('products_per_page', 9));
        }

        //фильтры товаров
        $root_category = $category->findRootCategory();
        $all_filters = ParentCatalogFilter::where('catalog_id', $root_category->id)
            ->public()
            ->orderBy('order')
            ->get();

        $filters_list = [];
        foreach ($all_filters as $filter) {
            $values = ProductChar::where('name', $filter->name)
                ->whereIn('catalog_id', $children_ids)
                ->select('value')
                ->distinct()
                ->pluck('value')
                ->all();
            natsort($values);
            $filters_list[$filter->name] = [
                'translit' => Text::translit($filter->name),
                'values' => $values
            ];
        }

        if (request()->ajax()) {
            //загрузить еще
            $view_items = [];
            foreach ($products as $item) {
                $view_items[] = view('catalog.product_item', ['product' => $item,])->render();
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
            'h1' => $category->getH1(),
            'text' => $category->text,
            'children' => $category->public_children,
            'products' => $products,
            'filters_list' => $filters_list,
            'filter_max_price' => $filter_max_price
        ];

        return view('catalog.category', $data);
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

//        $product_n = Product::where('id',1)->first(['id', 'name', 'price']);
//        dd($product_n);

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.catalog.productEdit', [$product->id]));
        }

        return view(
            'catalog.product',
            [
                'product' => $product,
                'h1' => $product->getH1(),
                'bread' => $bread,
                'text' => $product->text,
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
