<?php namespace App\Http\Controllers;

use Fanky\Admin\Models\City;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\Review;
use Fanky\Admin\Settings;
use Illuminate\Http\Response;

class WelcomeController extends Controller {

    public function index(): Response
    {
        $page = Page::find(1);
        $page->ogGenerate();
        $page->setSeo();

        $new_products = Product::public()
            ->where('is_new',1)
            ->with(['catalog'])
            ->get();

        $new_products_categories = [];
        foreach ($new_products as $product) {
            $main_category = $product->findRootParentCatalog($product->catalog_id);
            $new_products_categories[$main_category->name][] = $product;
        }

        return response()->view('pages.index', [
            'page' => $page,
            'text' => $page->text,
            'h1' => $page->getH1(),
            'new_products_categories' => $new_products_categories
        ]);
    }
}
