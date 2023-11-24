<?php namespace App\Http\Controllers;

use Fanky\Admin\Models\City;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Review;
use Fanky\Admin\Settings;
use Illuminate\Http\Response;

class WelcomeController extends Controller {

    public function index(): Response
    {
        $page = Page::find(1);
//        session()->forget('city_alias');
        $page->ogGenerate();
        $page->setSeo();
        $city_alias = session('city_alias');

        $main_reviews = Review::public()
            ->onMain()
            ->limit(Settings::get('main_review_count', 5))
            ->get();

        return response()->view('pages.index', [
            'page' => $page,
            'text' => $page->text,
            'h1' => $page->getH1(),
            'main_reviews' => $main_reviews,
            'city_alias' => $city_alias
        ]);
    }
}
