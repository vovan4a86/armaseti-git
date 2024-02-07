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
            $catalog_on_main = Catalog::query()
                ->public()
                ->onMain()
                ->with(['public_children', 'public_children.public_children'])
                ->orderBy('order')
                ->get();
            Cache::add('catalog_on_main', $catalog_on_main, now()->addMinutes(60));
        }

        $new_products = Cache::get('new_products', collect());
        if (!count($new_products)) {
            $new_products = Product::public()
                ->where('is_new',1)
                ->with(['catalog', 'images'])
                ->get();
            Cache::add('new_products', $new_products, now()->addMinutes(60));
        }

        $main_categories_ids = Cache::get('main_categories_ids', []);
        if (!count($main_categories_ids)) {
            foreach ($new_products as $product) {
                $main_category = $product->findRootParentCatalog($product->catalog_id);
                if (!in_array($main_category->id, $main_categories_ids)) {
                    $main_categories_ids[] = $main_category->id;
                }
            }
            natsort($main_categories_ids);
            Cache::add('main_categories_ids', $main_categories_ids, now()->addMinutes(60));
        }

        $new_products_categories = [];
        foreach ($main_categories_ids as $id) {
            $catalog = Catalog::find($id);
            $new_products_categories[$catalog->name] = $catalog->getRecurseProducts()
                ->where('is_new', 1)
                ->with('catalog', 'images')
                ->inRandomOrder()
                ->limit(Settings::get('new_products_per_tab', 6))
                ->get();
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
