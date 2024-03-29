<?php

namespace App\Http\Controllers;

use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\CatalogFilter;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\ParentCatalogFilter;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductChar;
use Fanky\Admin\Settings;
use Fanky\Admin\Text;
use Fanky\Auth\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use phpDocumentor\Reflection\Types\Collection;
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
        $page->setSeo();
        $page = $this->add_region_seo($page);

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
                ->whereCatalogId($category->id)
                ->first();
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
//        session()->forget('favorites');
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

        $canonical = null;
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

        //параметры строки
        $data_filter = request()->except(['price_from', 'price_to', 'in_stock']); //фильтры товаров
        $price_from = request()->get('price_from'); //встроенный фильтр
        $price_to = request()->get('price_to'); //встроенный фильтр
        $in_stock = request()->get('in_stock'); //встроенный фильтр
        $price_order = request()->get('price_order', session('price_order', 'asc')); //сортировка
        session(['price_order' => $price_order ?? 'asc']);
        $reset_filter = request()->get('reset'); //нажали кнопку сброса фильтра

        $query_string = '';
        $appends = [];
        if (!$reset_filter) {
            $query_string = '?price_from=' . $price_from . '&price_to=' . $price_to . '&in_stock=' . $in_stock;
            $appends = ['price_from' => $price_from, 'price_to' => $price_to, 'in_stock' => $in_stock];
        }
        //сброс кнопкой, цену/наличие не учитываем
        if ($reset_filter == 1) {
            $price_from = null;
            $price_to = null;
            $in_stock = null;
        }

        //если фильтровали по встроенным фильтрам (цена/наличие)
        if ($price_from || $price_to || $in_stock) {
            $products_query = Product::whereIn('catalog_id', $children_ids)
                ->where('in_stock', $in_stock)
                ->where('price', '>', $price_from)
                ->where('price', '<=', $price_to);

            $products_query->orderBy('price', $price_order);

            //фильтры товаров, кроме цены и наличия
            if (!count($data_filter)) {
                $products = $products_query
                    ->with(['images', 'catalog'])
                    ->paginate(Settings::get('products_per_page', 9))
                    ->appends($appends);
            } else {
                //формирование массива для запроса + appends
                //на входе массивы параметров разные, при нажатии кнопки/показать еще поэтому доп.проверка
                $result_filters = [];
                foreach ($data_filter as $name => $values) {
                    if (is_array($values)) {
                        foreach ($values as $val) {
                            $result_filters[] = $val;
                            $query_string .= '&' . $name . '[]=' . $val;
                            $appends[$name][] = $val;
                        }
                    } else {
                        $result_filters[] = $values;
                        $query_string .= '&' . $name . '=' . $values;
                        $appends[$name] = $values;
                    }
                }

                //фильтруем по характеристикам товара
                $products = $products_query
                    ->with(['images', 'chars', 'catalog'])
                    ->whereHas('chars', function(Builder $query) use ($result_filters) {
                        $query->whereIn('value', $result_filters);
                    })
                    ->paginate(Settings::get('products_per_page', 9))
                    ->appends($appends);
            }
        } else {
            //чистая загрузка страницы без фильтрации
            $products = Product::whereIn('catalog_id', $children_ids)
                ->public()
                ->orderBy('price', $price_order)
                ->with(['images', 'catalog'])
                ->paginate(Settings::get('products_per_page', 9));
            $query_string = '';
        }

        //макс цена для фильтра
        $filter_max_price = $category->getProductMaxPriceInCatalog();

        $all_filters = $category->getPublicRecurseFilterList();

        $filters_list = Cache::get('filters_list_' . $category->id, []);
        if (!count($filters_list)) {
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
            Cache::add('filters_list_' . $category->id, $filters_list, now()->addMinutes(60));
        }

        if (request()->ajax()) {
            //загрузить еще
            $view_items = [];
            foreach ($products as $item) {
                $view_items[] = view('catalog.product_item_catalog', ['product' => $item,])->render();
            }

            $btn_paginate = null;
            if ($products->nextPageUrl()) {
                $btn_paginate = view('paginations.load_more', ['paginator' => $products])->render();
            }

            $paginate = view('paginations.with_pages', ['paginator' => $products])->render();

            return [
                'items' => $view_items,
                'btn' => $btn_paginate,
                'paginate' => $paginate,
                'current_url' => $category->url . $query_string
            ];
        }

        $current_categories = count($category->public_children)
            ? $category->public_children
            : $category->public_parent->public_children;

        $data = [
            'bread' => $bread,
            'category' => $category,
            'gallery' => $category->images,
            'h1' => $category->getH1(),
            'text' => $category->text,
            'text_after' => $category->text_after,
            'canonical' => $canonical,
            'current_categories' => $current_categories,
            'products' => $products,
            'filters_list' => $filters_list,
            'filter_max_price' => $filter_max_price,
            'in_stock' => $in_stock,
            'data_filter' => $data_filter
        ];

        return view('catalog.category', $data);
    }

    public function product(Product $product)
    {
        $bread = $product->getBread();
        $product->generateTitle();
        $product->generateDescription();
        if (!$product->text) {
            $product->generateText();
        }
        $product->setSeo();
        $product->ogGenerate();
        $product = $this->add_region_seo($product);

        $image = null;
        if (count($product->images) == 0) {
            $image = Catalog::UPLOAD_URL . $product->catalog->image;
        }

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.catalog.productEdit', $product->id));
        }

        $related = [];

        $features = $product->catalog->getFeatures();
        $benefits = $product->catalog->getBenefits();

        return view(
            'catalog.product',
            [
                'product' => $product,
                'h1' => $product->getH1(),
                'bread' => $bread,
                'text' => $product->text,
                'image' => $image,
                'related' => $related,
                'features' => $features,
                'benefits' => $benefits,
            ]
        );
    }

    public function new()
    {
        $page = Page::getByPath(['new-products']);
        if (!$page) {
            return abort(404);
        }

        $bread = $page->getBread();
        $page->setSeo();
        $page = $this->add_region_seo($page);

        $price_from = request()->get('price_from'); //встроенный фильтр
        $price_to = request()->get('price_to'); //встроенный фильтр
        $in_stock = request()->get('in_stock'); //встроенный фильтр
        $price_order = request()->get('price_order', session('price_order', 'asc')); //сортировка
        session(['price_order' => $price_order]);
        $cat = request()->get('cat', 'all');

        if ($cat == 'all') {
            $products_query = Product::public()
                ->where('is_new', 1)
                ->with(['images', 'chars', 'catalog'])
                ->orderBy('price', $price_order);
        } else {
            $catalog = Catalog::find($cat);
            $ids = $catalog->getRecurseChildrenIds();
            $products_query = Product::public()
                ->whereIn('catalog_id', $ids)
                ->where('is_new', 1)
                ->with(['images', 'chars', 'catalog'])
                ->orderBy('price', $price_order);
        }
        $main_products_categories = Catalog::getNewProductsCategories();

        //макс цена для фильтра
        $filter_max_price = $products_query->max('price');

        if($price_to && $in_stock) {
            $products_query = $products_query->where('price', '>', $price_from)
                ->where('price', '<=', $price_to)
                ->where('in_stock', $in_stock);
            $query_string =  $cat == 'all' ? '?' : '&';
            $query_string .= 'price_from=' . $price_from . '&price_to=' . $price_to . '&in_stock=' . $in_stock;
            $appends = ['price_from' => $price_from, 'price_to' => $price_to, 'in_stock' => $in_stock];
            $products = $products_query
                ->paginate(Settings::get('products_per_page', 9))
                ->appends($appends);
        } else {
            $query_string = '';
            $products = $products_query->paginate(Settings::get('products_per_page', 9));
        }

        $current_url = $cat == 'all' ? route('new-products') . $query_string : route('new-products', ['cat' => $cat]) . $query_string;

        if (request()->ajax()) {
            //загрузить еще
            $view_items = [];
            foreach ($products as $item) {
                $view_items[] = view('catalog.product_item_catalog', ['product' => $item,])->render();
            }

            $btn_paginate = null;
            if ($products->nextPageUrl()) {
                $btn_paginate = view('paginations.load_more', ['paginator' => $products])->render();
            }

            $paginate = view('paginations.with_pages', ['paginator' => $products])->render();

            return [
                'items' => $view_items,
                'btn' => $btn_paginate,
                'paginate' => $paginate,
                'current_url' => $current_url,
            ];
        }

        return view(
            'catalog.new_products',
            [
                'h1' => $page->getH1(),
                'bread' => $bread,
                'products' => $products,
                'main_products_categories' => $main_products_categories,
                'filter_max_price' => $filter_max_price,
                'route' => route('new-products'),
                'current_url' => $current_url,
                'cat' => $cat
            ]
        );
    }

    public function search()
    {
        $bread[] = [
            'url' => route('search'),
            'name' => 'Поиск'
        ];

        $price_from = request()->get('price_from'); //встроенный фильтр
        $price_to = request()->get('price_to'); //встроенный фильтр
        $in_stock = request()->get('in_stock'); //встроенный фильтр
        $reset_filter = request()->get('reset'); //нажали кнопку сброса фильтра
        $price_order = request()->get('price_order', session('price_order', 'asc')); //сортировка
        session(['price_order' => $price_order ?? 'asc']);

        $query_string = '';
        $appends = [];
        if (!$reset_filter) {
            $query_string = '&price_from=' . $price_from . '&price_to=' . $price_to . '&in_stock=' . $in_stock;
            $appends = ['price_from' => $price_from, 'price_to' => $price_to, 'in_stock' => $in_stock];
        }
        //сброс кнопкой, цену/наличие не учитываем
        if ($reset_filter == 1) {
            $price_from = null;
            $price_to = null;
            $in_stock = null;
        }

        if ($s = Request::get('s')) {
            $query_string = '?s=' . $s;
            $products_query = Product::public()
                ->where('name', 'LIKE', '%' . $s . '%');
//                ->orWhere('article', 'LIKE', '%' . $s . '%');
            \Debugbar::log($products_query->count());


            //макс цена для фильтра
            $filter_max_price = $products_query->max('price');

            if ($price_from || $price_to || $in_stock) {
                $query_string .= '&price_from=' . $price_from . '&price_to=' . $price_to . '&in_stock=' . $in_stock;
                $products_query = $products_query
                    ->where('price', '>', $price_from)
                    ->where('price', '<=', $price_to)
                    ->where('in_stock', $in_stock);
                \Debugbar::log($products_query->count());
            }

            $products = $products_query
                ->orderBy('price', $price_order)
                ->with(['images', 'catalog'])
                ->paginate(Settings::get('search_per_page', 9))
                ->appends(array_merge(['s' => $s], $appends));
        } else {
            $filter_max_price = 0;
            $products = collect();
        }

        SEOMeta::setTitle('Результат поиска «' . $s . '»');

        if (request()->ajax()) {
            //загрузить еще
            $view_items = [];
            foreach ($products as $item) {
                $view_items[] = view('catalog.product_item_catalog', ['product' => $item,])->render();
            }

            $btn_paginate = null;
            if ($products->nextPageUrl()) {
                $btn_paginate = view('paginations.load_more', ['paginator' => $products])->render();
            }

            $paginate = view('paginations.with_pages', ['paginator' => $products])->render();

            // . '&page=' . $products->currentPage()
            return [
                'items' => $view_items,
                'btn' => $btn_paginate,
                'paginate' => $paginate,
                'current_url' => route('search') . $query_string
            ];
        }

        return view(
            'search.index',
            [
                'products' => $products,
                'bread' => $bread,
                'h1' => 'Результат поиска «' . $s . '»',
                'title' => 'Результат поиска «' . $s . '»',
                's' => $s,
                'route' => route('search'),
                'filter_max_price' => $filter_max_price,
                'in_stock' => $in_stock,
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
