<?php namespace App\Http\Controllers;

use App;
use App\Classes\SiteHelper;
use Fanky\Admin\Models\Handbook;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\Page;
use Fanky\Auth\Auth;
use Settings;
use View;

class HandbookController extends Controller {
	public $bread = [];
	protected $handbook_page;

	public function __construct() {
		$this->handbook_page = Page::whereAlias('handbook')
			->get()
			->first();
		$this->bread[] = [
			'url'  => route('handbook'),
			'name' => $this->handbook_page['name']
		];
	}

	public function index() {
		$page = $this->handbook_page;
		if (!$page)
			abort(404, 'Страница не найдена');
		$bread = $this->bread;
        $page->setSeo();
        $page->ogGenerate();

        $items = Handbook::orderBy('order')
            ->public()->paginate(Settings::get('handbook_per_page', 10));

        if (count(request()->query())) {
            View::share('canonical', route('handbook'));
        }

        return view('handbook.index', [
            'title' => $page->title,
            'text' => $page->text,
            'h1'    => $page->getH1(),
            'bread' => $bread,
            'items' => $items,
            'top_view' => $this->handbook_page->getTopView()
        ]);
	}

	public function item($alias) {
		$item = Handbook::whereAlias($alias)->public()->first();
        if (!$item) abort(404);

        $bread = $this->bread;
        $bread[] = [
            'url' => $item->url,
            'name' => $item->name
        ];
        $item->ogGenerate();

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.handbook.edit', [$item->id]));
        }

        $item->setSeo();

        $city = SiteHelper::getCurrentCity();
        $search = ['{city}', '{city_name}'];
        if ($city) {
            $replace = [' в ' . $city->in_city, $city->name];
            $item->text = SiteHelper::replaceLinkToRegion($item->text, $city);
        } else {
            $replace = [' в Екатеринбурге', 'Екатеринбург'];
        }
        $item->text = str_replace($search, $replace, $item->text);

		return view('handbook.item', [
			'article'        => $item,
            'h1'          => $item->getH1(),
			'text'        => $item->text,
            'bread' => $bread,
            'top_view' => $this->handbook_page->getTopView()
		]);
	}
}
