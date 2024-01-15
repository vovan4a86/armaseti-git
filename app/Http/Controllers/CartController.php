<?php
namespace App\Http\Controllers;

use App;
use App\Classes\SiteHelper;
use Artesaos\SEOTools\Facades\SEOMeta;
use Cart;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\Order;
use Fanky\Admin\Models\Page;
use Fanky\Auth\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Settings;
use View;

class CartController extends Controller
{
    public $bread = [];
    protected $cart_page;

    public function __construct()
    {
        $this->cart_page = Page::whereAlias('cart')
            ->get()
            ->first();
        $this->bread[] = [
            'url' => route('cart'),
            'name' => $this->cart_page['name']
        ];
    }

    public function index()
    {
        $page = $this->cart_page;
        if (!$page) {
            abort(404, 'Страница не найдена');
        }
        $bread = $this->bread;
        $page->ogGenerate();
        $page->setSeo();

        $items = Cart::all();

        return view(
            'cart.index',
            [
                'h1' => $page->getH1(),
                'bread' => $bread,
                'items' => $items
            ]
        );
    }

    protected function formatValidationErrors(Validator $validator): array
    {
        $msg = $validator->errors()->all('<p>:message</p>');

        return ['error' => true, 'msg' => implode('', $msg)];
    }

    public function success() {
        $bread = $this->bread;
        $bread[] = [
            'url' => route('order-success'),
            'name' => 'Успешная отправка'
        ];

        SEOMeta::setTitle('Успешная отправка');

        return view('cart.success', compact('bread'));
    }
}
