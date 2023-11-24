<?php namespace App\Http\Controllers;

use App;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Review;
use Fanky\Auth\Auth;
use Settings;
use View;

class ReviewsController extends Controller {
	public $bread = [];
	protected $reviews_page;

	public function __construct() {
		$this->reviews_page = Page::whereAlias('reviews')
			->get()
			->first();
		$this->bread[] = [
			'url'  => route('reviews'),
			'name' => $this->reviews_page['name']
		];
	}

	public function index() {
		$page = $this->reviews_page;
		if (!$page)
			abort(404, 'Страница не найдена');
		$bread = $this->bread;

        $items = Review::orderBy('date', 'desc')
            ->public()->paginate(Settings::get('review_per_page', 9));

        if (count(request()->query())) {
            View::share('canonical', route('reviews'));
        }

        return view('reviews.index', [
            'title' => $page->title,
            'h1'    => $page->getH1(),
            'bread' => $bread,
            'items' => $items,
            'top_view' => $this->reviews_page->getTopView()
        ]);
	}

	public function item($alias) {
		$item = Review::whereAlias($alias)->public()->first();
        if (!$item) abort(404);

        $bread = $this->bread;
        $bread[] = [
            'url' => $item->url,
            'name' => $item->name
        ];

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.reviews.edit', [$item->id]));
        }

		return view('news.item', [
			'article'        => $item,
            'h1'          => $item->getH1(),
			'text'        => $item->text,
            'bread' => $bread,
            'top_view' => $this->reviews_page->getTopView()
        ]);
	}
}
