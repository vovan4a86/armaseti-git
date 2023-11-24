<?php namespace App\Http\Controllers;

use App;
use App\Classes\SiteHelper;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\Page;
use Fanky\Auth\Auth;
use Settings;
use View;

class NewsController extends Controller {
	public $bread = [];
	protected $news_page;

	public function __construct() {
		$this->news_page = Page::whereAlias('news')
			->get()
			->first();
		$this->bread[] = [
			'url'  => route('news'),
			'name' => $this->news_page['name']
		];
	}

	public function index() {
		$page = $this->news_page;
		if (!$page)
			abort(404, 'Страница не найдена');
		$bread = $this->bread;
        $page->ogGenerate();
        $page->setSeo();

        $items = News::orderBy('date', 'desc')
            ->public()->paginate(Settings::get('news_per_page', 9));

        if (count(request()->query())) {
            View::share('canonical', route('news'));
        }

        return view('news.index', [
            'title' => $page->title,
            'text' => $page->text,
            'h1'    => $page->getH1(),
            'bread' => $bread,
            'items' => $items,
            'top_view' => $this->news_page->getTopView()
        ]);
	}

	public function item($alias) {
		$item = News::whereAlias($alias)->public()->first();
        if (!$item) abort(404);

        $bread = $this->bread;
        $bread[] = [
            'url' => $item->url,
            'name' => $item->name
        ];

        $news_related = News::where('id', '!=', $item->id)
            ->orderBy('date', 'desc')
            ->public()
            ->limit(Settings::get('news_related_count', 6))
            ->get();


        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.news.edit', [$item->id]));
        }

        $item->setSeo();
        $item->ogGenerate();

        $city = SiteHelper::getCurrentCity();
        $search = ['{city}', '{city_name}'];
        if ($city) {
            $replace = [' в ' . $city->in_city, $city->name];
            $item->text = SiteHelper::replaceLinkToRegion($item->text, $city);
        } else {
            $replace = [' в Екатеринбурге', 'Екатеринбург'];
        }
        $item->text = str_replace($search, $replace, $item->text);

		return view('news.item', [
			'article'        => $item,
            'h1'          => $item->getH1(),
			'text'        => $item->text,
            'bread' => $bread,
            'news_related' => $news_related,
            'top_view' => $this->news_page->getTopView()
        ]);
	}
}
