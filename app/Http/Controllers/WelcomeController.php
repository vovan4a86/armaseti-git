<?php namespace App\Http\Controllers;

use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\City;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\Review;
use Fanky\Admin\Settings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class WelcomeController extends Controller {

    public function index(): Response
    {
        $page = Page::find(1);
        $page->ogGenerate();
        $page->setSeo();

        $catalog_on_main = Cache::get('catalog_on_main', collect());
        if (!count($catalog_on_main)) {
            \Debugbar::log('catalog_on_main from DB');
            $catalog_on_main = Catalog::query()
                ->public()
                ->onMain()
                ->with(['public_children'])
                ->orderBy('order')
                ->get();
            Cache::add('catalog_on_main', $catalog_on_main, now()->addMinutes(60));
        } else {
            \Debugbar::log('catalog_on_main from cache');
        }

        $new_products = Product::public()
            ->where('is_new',1)
            ->with(['catalog', 'images'])
            ->get();

        $new_products_categories = [];
        foreach ($new_products as $product) {
            $main_category = $product->findRootParentCatalog($product->catalog_id);
            $new_products_categories[$main_category->name][] = $product;
        }

        $news = News::public()->onMain()->orderBy('date', 'desc')->get();

        return response()->view('pages.index', [
            'page' => $page,
            'text' => $page->text,
            'h1' => $page->getH1(),
            'new_products_categories' => $new_products_categories,
            'catalog_on_main' => $catalog_on_main,
            'news' => $news
        ]);
    }
}
