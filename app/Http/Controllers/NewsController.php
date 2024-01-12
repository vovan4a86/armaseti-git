<?php
namespace App\Http\Controllers;

use App;
use App\Classes\SiteHelper;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\Page;
use Fanky\Auth\Auth;
use Settings;
use View;

class NewsController extends Controller
{
    public $bread = [];
    protected $news_page;

    public function __construct()
    {
        $this->news_page = Page::whereAlias('news')
            ->get()
            ->first();
        $this->bread[] = [
            'url' => route('news'),
            'name' => $this->news_page['name']
        ];
    }

    public function index()
    {
        $page = $this->news_page;
        if (!$page) {
            abort(404, 'Страница не найдена');
        }
        $bread = $this->bread;
        $page->ogGenerate();
        $page->setSeo();

        $items = News::orderBy('date', 'desc')
            ->public()->where('aside', 0)->paginate(Settings::get('news_per_page', 6));

        $aside_items = News::orderBy('date', 'desc')
            ->public()->where('aside', 1)->paginate(Settings::get('news_per_page', 6));

        if (count(request()->query())) {
            View::share('canonical', route('news'));
        }

        //обработка ajax-обращений, в routes добавить POST метод(!)
        if (request()->ajax()) {
            $view_items = [];
            foreach ($items as $item) {
                //добавляем новые элементы
                $view_items[] = view(
                    'news.newses_item',
                    [
                        'item' => $item,
                    ]
                )->render();
            }

            $btn_paginate = null;
            if ($items->nextPageUrl()) {
                $btn_paginate = view('paginations.load_more', ['paginator' => $items])->render();
            }

            $paginate = view('paginations.with_pages', ['paginator' => $items])->render();

            return [
                'items' => $view_items,
                'btn' => $btn_paginate,
                'paginate' => $paginate
            ];
        }

        return view(
            'news.index',
            [
                'title' => $page->title,
                'text' => $page->text,
                'h1' => $page->getH1(),
                'bread' => $bread,
                'items' => $items,
                'aside_items' => $aside_items
            ]
        );
    }

    public function item($alias)
    {
        $item = News::whereAlias($alias)->public()->first();
        if (!$item) {
            abort(404);
        }

        $bread = $this->bread;
        $bread[] = [
            'url' => $item->url,
            'name' => $item->name
        ];

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.news.edit', [$item->id]));
        }

        $item->setSeo();
        $item->ogGenerate();

        return view(
            'news.item',
            [
                'item' => $item,
                'h1' => $item->getH1(),
                'text' => $item->text,
                'text_after' => $item->text_after,
                'bread' => $bread,
            ]
        );
    }
}
