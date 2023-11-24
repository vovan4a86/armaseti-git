<?php namespace App\Http\Controllers;

use App;
use Fanky\Admin\Models\City;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\SearchIndex;
use Fanky\Auth\Auth;
use Illuminate\Http\Response;
use Request;
use SiteHelper;
use View;

class PageController extends Controller {

    public function region_index($city_alias): Response
    {
        $this->city = City::current($city_alias);
        return $this->page();
    }

    public function region_page($alias)
    {
        return redirect(route('default', [$alias]), 301);
    }

    public function page($alias = null): Response
    {
        $path = explode('/', $alias);
        if (!$alias) {
            $current_city = SiteHelper::getCurrentCity();
            $this->city = $current_city && $current_city->id ? $current_city : null;
            $page = $this->city->generateIndexPage();
        } else {
            $page = Page::getByPath($path);
            if (!$page) {
                abort(404, 'Страница не найдена');
            }
        }

        $bread = $page->getBread();
        $page->h1 = $page->getH1();
        $view = $page->getView();
        $page->ogGenerate();
        $page->setSeo();

        Auth::init();
        if (Auth::user() && Auth::user()->isAdmin) {
            View::share('admin_edit_link', route('admin.pages.edit', [$page->id]));
        }

        return response()->view($view, [
            'page' => $page,
            'h1' => $page->h1,
            'text' => $page->text,
            'title' => $page->title,
            'bread' => $bread,
            'top_view' => $page->getTopView()
        ]);
    }

    public function policy()
    {
        $page = Page::whereAlias('policy')->first();
        if (!$page)
            abort(404, 'Страница не найдена');
        $bread = $page->getBread();
        $page->ogGenerate();
        $page->setSeo();

        return view('pages.text', [
            'page' => $page,
            'text' => $page->text,
            'h1'    => $page->getH1(),
            'bread' => $bread,
            'top_view' => $page->getTopView()
        ]);
    }

    public function search() {
        \View::share('canonical', route('search'));
        $q = Request::get('q', '');

        if (!$q) {
            $items_ids = [];
        } else {
            $items_ids = SearchIndex::orWhere('name', 'LIKE', '%' . $q . '%')
                ->orderByDesc('updated_at')
                ->pluck('product_id')->all();
        }
        $items = Product::whereIn('id', $items_ids)
            ->paginate(10)
            ->appends(['q' => $q]); //Добавить параметры в строку запроса можно через метод appends().

        if (Request::ajax()) {
            $view_items = [];
            foreach ($items as $item) {
                $view_items[] = view('search.search_item', [
                    'item' => $item,
                ])->render();
            }

            return response()->json([
                'items' => $view_items,
                'paginate' => view('paginations.with_pages', [
                    'paginator' => $items
                ])->render()
            ]);
        }

        return view('search.index', [
            'items' => $items,
            'title' => 'Результат поиска «' . $q . '»',
            'query' => $q,
            'name' => 'Поиск ' . $q,
            'keywords' => 'Поиск ',
            'description' => 'Поиск ',
            'headerIsWhite' => true,
            'top_view' => Page::TOP_VIEW_DEFAULT
        ]);
    }

    public function robots() {
        $robots = new App\Robots();
        if (App::isLocal()) {
            $robots->addUserAgent('*');
            $robots->addDisallow('/');
        } else {
            $robots->addUserAgent('*');
            $robots->addDisallow('/admin');
            $robots->addDisallow('/ajax');
        }

        $robots->addHost(config('app.url'));
        $robots->addSitemap(secure_url('sitemap.xml'));

        $response = response($robots->generate())
            ->header('Content-Type', 'text/plain; charset=UTF-8');
        $response->header('Content-Length', strlen($response->getOriginalContent()));

        return $response;
    }
}
