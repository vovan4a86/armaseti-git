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
use Fanky\Admin\Models\Product;
use Illuminate\Http\Request;
use Mail;

use Cart;
use Settings;
use SiteHelper;
use Validator;

class AjaxController extends Controller
{
    private $fromMail = 'info@luxkraft.ru';
    private $fromName = 'Luxkraft';

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

            $prodImage = $product->image()->first();
            if ($prodImage) {
                $product_item['image'] = $prodImage->thumb(2);
            } else {
                $image = Catalog::whereId($product->catalog_id)->first()->image;
                if ($image) {
                    $product_item['image'] = Catalog::UPLOAD_URL . $image;
                } else {
                    $product_item['image'] = Product::NO_IMAGE;
                }
            }

            Cart::add($product_item);
        }

        $header_cart = view('blocks.header_cart')->render();
        $btn = view('catalog.product_btn', ['name' => $product->name, 'in_cart' => true])->render();
        $card_btn = view('catalog.card_btn', ['product' => $product, 'in_cart' => true])->render();

        return [
            'success' => true,
            'header_cart' => $header_cart,
            'btn' => $btn,
            'card_btn' => $card_btn
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
        Cart::remove($id);
        $total_count = Cart::count();
        $total_sum = Cart::sum();
        $summary = view('cart.summary', compact('total_count', 'total_sum'))
            ->render();
        $header_cart = view('blocks.header_cart')->render();
        return [
            'success' => true,
            'summary' => $summary,
            'header_cart' => $header_cart,
            'total_count' => $total_count,
            'total_sum' => $total_sum
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

    //прайс
    public function postRequestPrice(Request $request): array
    {
        $data = $request->only(['email']);
        $valid = Validator::make(
            $data,
            [
                'email' => 'required',
            ],
            [
                'email.required' => 'Не заполнено поле Email',
            ]
        );

        if ($valid->fails()) {
            return ['errors' => $valid->messages()];
        } else {
            $feedback_data = [
                'type' => 4,
                'data' => $data
            ];
            $feedback = Feedback::create($feedback_data);
            Mail::send(
                'mail.feedback',
                ['feedback' => $feedback],
                function ($message) use ($feedback) {
                    $title = $feedback->id . ' | Запрос прайс-листа | Luxkraft';
                    $message->from($this->fromMail, $this->fromName)
                        ->to(Settings::get('feedback_email'))
                        ->subject($title);
                }
            );

            return ['success' => true];
        }
    }

    //расчет цены
    public function postCalc(Request $request): array
    {
        $data = $request->only(['name', 'phone', 'text']);
        $valid = Validator::make(
            $data,
            [
                'name' => 'required',
                'phone' => 'required',
            ],
            [
                'name.required' => 'Не заполнено поле Имя',
                'phone.required' => 'Не заполнено поле Телефон',
            ]
        );

        if ($valid->fails()) {
            return ['errors' => $valid->messages()];
        } else {
            $feedback_data = [
                'type' => 2,
                'data' => $data
            ];
            $feedback = Feedback::create($feedback_data);
            Mail::send(
                'mail.feedback',
                ['feedback' => $feedback],
                function ($message) use ($feedback) {
                    $title = $feedback->id . ' | Расчет цены | Luxkraft';
                    $message->from($this->fromMail, $this->fromName)
                        ->to(Settings::get('feedback_email'))
                        ->subject($title);
                }
            );

            return ['success' => true];
        }
    }

    //закажите звонок
    public function postCallback(Request $request): array
    {
        $data = $request->only(['name', 'phone']);
        $valid = Validator::make(
            $data,
            [
                'name' => 'required',
                'phone' => 'required',
            ],
            [
                'name.required' => 'Не заполнено поле Имя',
                'phone.required' => 'Не заполнено поле Phone',
            ]
        );

        if ($valid->fails()) {
            return ['errors' => $valid->messages()];
        } else {
            $feedback_data = [
                'type' => 1,
                'data' => $data
            ];
            $feedback = Feedback::create($feedback_data);
            Mail::send(
                'mail.feedback',
                ['feedback' => $feedback],
                function ($message) use ($feedback) {
                    $title = $feedback->id . ' | Заказ звонка | Luxkraft';
                    $message->from($this->fromMail, $this->fromName)
                        ->to(Settings::get('feedback_email'))
                        ->subject($title);
                }
            );

            return ['success' => true];
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

    //ОФОРМЛЕНИЕ ЗАКАЗА
    public function postSendCartOrder(Request $request): array
    {
        $data = $request->only(
            [
                'receiver',
                'phone',
                'email',
                'city',
                'street',
                'building',
                'room',
                'message',
                'delivery',
                'payment'
            ]
        );

        $messages = array(
            'receiver.required' => 'Не заполнено поле Получатель',
            'phone.required' => 'Не заполнено поле Телефон',
            'email.required' => 'Не заполнено поле Email',
        );

        $valid = Validator::make(
            $data,
            [
                'receiver' => 'required',
                'phone' => 'required',
                'email' => 'required',
            ],
            $messages
        );
        if ($valid->fails()) {
            return ['errors' => $valid->messages()];
        }

        $data['total_sum'] = Cart::sum();
        $data['unique_id'] = time();
        session(['therm_order_id' => $data['unique_id']]);

        $order = Order::query()->create($data);
        $items = Cart::all();

        foreach ($items as $item) {
            $itemPrice = $item['price'] * $item['count'];
            $order->products()->attach(
                $item['id'],
                [
                    'count' => $item['count'],
                    'price' => $itemPrice,
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

        Mail::send(
            'mail.new_order_table',
            [
                'order' => $order,
                'items' => $order_items,
                'all_count' => $all_count,
                'all_sum' => $all_sum
            ],
            function ($message) use ($order) {
                $title = $order->id . ' | Новый заказ | Thermoline';
                $message->from($this->fromMail, $this->fromName)
                    ->to(Settings::get('feedback_email'))
                    ->subject($title);
            }
        );

        Cart::purge();

        return ['success' => true, 'redirect' => route('cart.success')];
    }

    public function search(Request $request)
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

    public function confirmCity()
    {
        session(['confirm_city' => true]);

        return ['success' => true];
    }

    public function unConfirmCity()
    {
        session(['confirm_city' => null]);

        return ['success' => true];
    }

    public function postUpdateCatalogFilter()
    {
        $id = request()->get('id');

        if (!$id) return ['success' => false, 'msg' => 'Ошибка, нет id'];

        $item = CatalogFilter::where('id', $id)->first();
        if($item->published == 1) $item->update(['published' => 0]);
        else $item->update(['published' => 1]);

        return ['success' => true, 'msg' => 'Успешно обновлено!'];
    }
}
