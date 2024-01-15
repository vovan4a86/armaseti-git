<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\CatalogFilter;
use Fanky\Admin\Models\City;
use Fanky\Admin\Models\Feedback;
use Fanky\Admin\Models\Order as Order;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\ParentCatalogFilter;
use Fanky\Admin\Models\Product;
use Illuminate\Http\Request;
use Mail;

use Cart;
use Settings;
use SiteHelper;
use Symfony\Component\Finder\Finder;
use Validator;

class AjaxController extends Controller
{
    private $fromMail = 'info@armaseti.ru';
    private $fromName = 'Армасети';

    //РАБОТА С КОРЗИНОЙ
    public function postAddToCart(Request $request): array
    {
        $id = $request->get('id');
        $count = $request->get('count');

        $product = Product::find($id);
        if ($product) {
            $product_item['id'] = $product->id;
            $product_item['name'] = $product->name;
            $product_item['price'] = $product->price;
            $product_item['count'] = $count;
            $product_item['url'] = $product->url;
            $product_item['active'] = true;
            $product_item['article'] = $product->article;

            $product_image = $product->images()->first();
            if ($product_image) {
                $product_item['image'] = $product_image->thumb(1, $product->catalog->alias);
            }
//            else {
//                $image = Catalog::whereId($product->catalog_id)->first()->image;
//                if ($image) {
//                    $product_item['image'] = Catalog::UPLOAD_URL . $image;
//                } else {
//                    $product_item['image'] = Product::NO_IMAGE;
//                }
//            }

            Cart::add($product_item);
        }

        $header_cart = view('blocks.header_cart')->render();
//        $btn = view('catalog.product_btn', ['name' => $product->name, 'in_cart' => true])->render();
//        $card_btn = view('catalog.card_btn', ['product' => $product, 'in_cart' => true])->render();

        return [
            'success' => true,
            'header_cart' => $header_cart,
//            'btn' => $btn,
//            'card_btn' => $card_btn
        ];
    }

    public function postEditCartProduct(Request $request): array
    {
        $id = $request->get('id');
        $count = $request->get('count', 1);
        /** @var Product $product */
        $product = Product::find($id);
        if ($product) {
            $product_item['image'] = $product->showAnyImage();
            $product_item = $product->toArray();
            $product_item['count_per_tonn'] = $count;
            $product_item['url'] = $product->url;

            Cart::add($product_item);
        }

        $popup = view('blocks.cart_popup', $product_item)->render();

        return ['cart_popup' => $popup];
    }

    public function postUpdateToCart(Request $request): array
    {
        $id = $request->get('id');
        $count = $request->get('count');

        Cart::updateCount($id, $count);
        $total_count = Cart::count();
        $total_sum = Cart::sum();
        $summary = view('cart.summary', compact('total_count', 'total_sum'))
            ->render();

        return [
            'success' => true,
            'summary' => $summary
        ];
    }

    public function postRemoveFromCart(Request $request): array
    {
        $id = $request->get('id');
        Cart::deleteItem($id);
        $cart = Cart::all();

        $header_cart = view('blocks.header_cart')->render();
        $del_cart_item = view('cart.table_row_del', ['item' => $cart[$id]])->render();
        $cart_total = view('cart.cart_total')->render();

        return [
            'success' => true,
            'header_cart' => $header_cart,
            'del_cart_item' => $del_cart_item,
            'cart_total' => $cart_total
        ];
    }

    public function postRestoreFromCart(Request $request): array
    {
        $id = $request->get('id');
        Cart::restoreItem($id);
        $cart = Cart::all();

        $header_cart = view('blocks.header_cart')->render();
        $restore_cart_item = view('cart.table_row', ['item' => $cart[$id]])->render();
        $cart_total = view('cart.cart_total')->render();

        return [
            'success' => true,
            'header_cart' => $header_cart,
            'restore_cart_item' => $restore_cart_item,
            'cart_total' => $cart_total
        ];
    }

    public function postUpdateCount(Request $request): array
    {
        $id = $request->get('id');
        $count = $request->get('count');

        Cart::updateCount($id, $count);
        $cart = Cart::all();

        $row_summary = view('cart.table_row_summary', ['item' => $cart[$id]])
            ->render();

        $cart_total = view('cart.cart_total')->render();

        return [
            'success' => true,
            'row_summary' => $row_summary,
            'cart_total' => $cart_total
        ];
    }

    public function postPurgeCart(): array
    {
        Cart::purge();
        $total = view('cart.table_row_total')->render();
        $header_cart = view('blocks.header_cart')->render();
        return [
            'success' => true,
            'total' => $total,
            'header_cart' => $header_cart
        ];
    }

    //Отправить запрос = ОФОРМЛЕНИЕ ЗАКАЗА
    public function postRequest(Request $request): array
    {
        $data = $request->only(['name', 'phone', 'email', 'message']);
        $file = $request->file('file');
        $details = $request->file('details');

        $valid = Validator::make(
            $data,
            [
                'email' => 'required',
            ],
            [
                'email.required' => 'Не заполнено поле email',
            ]
        );

        if ($valid->fails()) {
            return ['errors' => $valid->messages()];
        } else {
            if ($file) {
                $file_name = md5(uniqid(rand(), true)) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path(Order::UPLOAD_URL), $file_name);
                $data['file'] = $file_name;
            }
            if ($details) {
                $file_name = md5(uniqid(rand(), true)) . '.' . $file->getClientOriginalExtension();
                $details->move(public_path(Order::UPLOAD_URL), $file_name);
                $data['details'] = $file_name;
            }

            $order = Order::create($data);
            $items = Cart::all();

            foreach ($items as $item) {
                $order->products()->attach(
                    $item['id'],
                    [
                        'count' => $item['count'],
                        'price' => $item['price'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                );
            }

            $order_items = $order->products;
            $all_count = 0;
            $all_sum = 0;
            foreach ($order_items as $item) {
                $all_sum += $item->pivot->price;
                $all_count += $item->pivot->count;
            }
            $order->update(['total_sum' => $all_sum]);

            Mail::send(
                'mail.new_order_table',
                [
                    'order' => $order,
                    'items' => $order_items,
                    'all_count' => $all_count,
                    'all_sum' => $all_sum
                ],
                function ($message) use ($order) {
                    $title = $order->id . ' | Заявка | Армасети';
                    $message->from($this->fromMail, $this->fromName)
                        ->to(Settings::get('feedback_email'))
                        ->subject($title);
                }
            );

            Cart::purge();

            return ['success' => true, 'redirect' => route('order-success')];
        }
    }

    //обратная связь
    public function postFeedback(Request $request): array
    {
        $data = $request->only(['name', 'email', 'message']);
        $valid = Validator::make(
            $data,
            [
                'name' => 'required',
                'email' => 'required',
                'message' => 'required',
            ],
            [
                'name.required' => 'Не заполнено поле Имя',
                'email.required' => 'Не заполнено поле Email',
                'message.required' => 'Не заполнено поле Сообщение',
            ]
        );

        if ($valid->fails()) {
            return ['errors' => $valid->messages()];
        } else {
            $feedback_data = [
                'type' => 3,
                'data' => $data
            ];
            $feedback = Feedback::create($feedback_data);
            Mail::send(
                'mail.feedback',
                ['feedback' => $feedback],
                function ($message) use ($feedback) {
                    $title = $feedback->id . ' | Обратная связь | Luxkraft';
                    $message->from($this->fromMail, $this->fromName)
                        ->to(Settings::get('feedback_email'))
                        ->subject($title);
                }
            );

            return ['success' => true];
        }
    }

    public function search(Request $request): array
    {
        $data = $request->only(['search']);

        $items = null;

        $page = Page::getByPath(['search']);
        $bread = $page->getBread();

        return [
            'success' => true,
            'redirect' => url(
                '/search',
                [
                    'bread' => $bread,
                    'items' => $items,
                    'data' => $data,
                ]
            )
        ];

//        return view('search.index', [
//            'bread' => $bread,
//            'items' => $items,
//            'data' => $data,
//        ]);

    }

    public function postApplyFilter($category_id): array
    {
        $data_filter = request()->except(['price_from', 'price_to', 'in_stock']);
        $price_from = request()->get('price_from');
        $price_to = request()->get('price_to');
        $in_stock = request()->get('in_stock');

        $catalog = Catalog::find($category_id);
        $children_ids = $catalog->getRecurseChildrenIds();

        //параметры для строки браузера
        $query = '?price_from=' . $price_from . '&price_to=' . $price_to . '&in_stock=' . $in_stock;
        $appends = ['price_from' => $price_from, 'price_to' => $price_to, 'in_stock' => $in_stock];

        $products_query = Product::whereIn('catalog_id', $children_ids)
            ->where('in_stock', $in_stock)
            ->where('price', '>', $price_from)
            ->where('price', '<=', $price_to);

        if (!count($data_filter)) {
            $products = $products_query
                ->paginate(Settings::get('products_per_page', 9))
                ->appends($appends);
        } else {
            $result_filters = [];
            foreach ($data_filter as $name => $values) {
                foreach ($values as $val) {
                    $result_filters[] = ['value', $val];
                    $query .= '&' . $name . '[]=' . $val;
                    if (count($values) == 1) {
                        $appends[$name] = $val;
                    } else {
                        $appends[$name][] = $val;
                    }
                }
            }

            $products = $products_query
                ->whereHas('chars', function ($query) use ($result_filters) {
                    $query->orWhere($result_filters);
                })
                ->paginate(Settings::get('products_per_page', 9))
                ->appends($appends);
        }

        $page = $products->currentPage();
        if ($page > 1) {
            $query .= '&page=' . $page;
        }

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
            'paginate' => $paginate,
            'current_url' => $catalog->url . $query
        ];
    }

    public function postFavorite(): array
    {
        $id = \request()->get('id');

        if (!$id) {
            return ['success' => false, 'msg' => 'Нет ID'];
        }

        $favorites = \Session::get('favorites', []);

        $add = true;
        if (count($favorites) == 0) {
            \Session::push('favorites', $id);
        } else {
            if (!in_array($id, $favorites)) {
                \Session::push('favorites', $id);
            } else {
                foreach ($favorites as $key => $item) {
                    if ($item == $id) {
                        unset($favorites[$key]);
                    }
                }
                \Session::forget('favorites');
                \Session::put('favorites', $favorites);
                $add = false;
            }
        }

        return ['success' => true, 'count' => count(\Session::get('favorites')), 'add' => $add];
    }

    public function postCompare(): array
    {
        $id = \request()->get('id');

        if (!$id) {
            return ['success' => false, 'msg' => 'Нет ID'];
        }

        $compare = \Session::get('compare', []);

        $add = true;
        if (count($compare) == 0) {
            \Session::push('compare', $id);
        } else {
            if (!in_array($id, $compare)) {
                \Session::push('compare', $id);
            } else {
                foreach ($compare as $key => $item) {
                    if ($item == $id) {
                        unset($compare[$key]);
                    }
                }
                \Session::forget('compare');
                \Session::put('compare', $compare);
                $add = false;
            }
        }

        return ['success' => true, 'count' => count(\Session::get('compare')), 'add' => $add];
    }

    public function postCompareDelete(): array
    {
        $id = \request()->get('id');

        if (!$id) {
            return ['success' => false, 'msg' => 'Нет ID'];
        }

        $compare = \Session::get('compare', []);
        foreach ($compare as $key => $item) {
            if ($item == $id) {
                unset($compare[$key]);
            }
        }
        \Session::forget('compare');
        \Session::put('compare', $compare);

        return ['success' => true, 'count' => count(\Session::get('compare'))];
    }

    //РАБОТА С ГОРОДАМИ
    public function postSetCity(Request $request)
    {
        $city_id = $request->get('city_id');
        $city = City::find($city_id);
        if ($city) {
            $result = [
                'success' => true,
            ];
            session(['city_alias' => $city->alias]);

            return response(json_encode($result))->withCookie(cookie('city_id', $city->id));
        } elseif ($city_id == 0) {
            $result = [
                'success' => true,
            ];
            session(['city_alias' => '']);

            return response(json_encode($result))->withCookie(cookie('city_id', 0));
        }

        return ['success' => false, 'msg' => 'Город не найден'];
    }

    public function postSetDefaultCity()
    {
        session(['city_alias' => '']);

        return response(json_encode(['success' => true]))->withCookie('city_id', 0);
    }

    public function postGetCorrectRegionLink(Request $request)
    {
        $city_id = $request->get('city_id');
        $city = City::find($city_id);
        $cur_url = $request->get('cur_url');
        $url = $cur_url;
        $excludeRegionAlias = false;
        foreach (Page::$excludeRegionAlias as $alias) {
            if (strpos($url, $alias) === 0) {
                $excludeRegionAlias = true;
                break;
            }
        }

        if ($cur_url != '/' && !$excludeRegionAlias) {
            $path = explode('/', $cur_url);
            $cities = City::pluck('alias')->all();
            /* проверяем - региональная ссылка или федеральная */
            if (in_array($path[3], $cities)) {
                if ($city) {
                    $path[3] = $city->alias;
                } else {
                    array_shift($path);
                }
            } else {
                if ($city) {
                    array_splice($path, 3, 0, $city->alias);
                }
            }

            $url = implode('/', $path);
        }

        return ['redirect' => url($url)];
    }

    public function showCitiesPopup()
    {
        $cities = City::query()->orderBy('name')
            ->get(['id', 'alias', 'name', DB::raw('LEFT(name,1) as letter')]);
        $citiesArr = [];
        if (count($cities)) {
            foreach ($cities as $city) {
                $citiesArr[$city->letter][] = $city; //Группировка по первой букве
            }
        }

        $mainCities = City::query()->orderBy('name')
            ->whereIn(
                'id',
                [
                    3, // msk
                    5, //spb
                ]
            )->get(['id', 'alias', 'name']);
        $curUrl = url()->previous() ?: '/';
        $curUrl = str_replace(url('/') . '/', '', $curUrl);

        $current_city = SiteHelper::getCurrentCity();

        return view(
            'blocks.popup_cities',
            [
                'cities' => $citiesArr,
                'mainCities' => $mainCities,
                'curUrl' => $curUrl,
                'current_city' => $current_city,
            ]
        );
    }
}
