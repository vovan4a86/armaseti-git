<?php

namespace App\Providers;

use Cache;
use Fanky\Admin\Models\City;
use Illuminate\Support\ServiceProvider;
use View;
use Fanky\Admin\Models\Page;

class SiteServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // пререндер для шаблона
        View::composer(
            ['template'],
            function (\Illuminate\View\View $view) {
                $header_menu = Cache::get('header_menu', collect());
                if (!count($header_menu)) {
                    $header_menu = Page::query()
                        ->public()
                        ->where('on_header', 1)
                        ->orderBy('order')
                        ->get();
                    Cache::add('header_menu', $header_menu, now()->addMinutes(60));
                }

                $mobile_menu = Cache::get('mobile_menu', collect());
                if (!count($mobile_menu)) {
                    $mobile_menu = Page::query()
                        ->public()
                        ->where('on_mobile', 1)
                        ->orderBy('order')
                        ->get();
                    Cache::add('mobile_menu', $mobile_menu, now()->addMinutes(60));
                }

                $footer_menu = Cache::get('footer_menu', collect());
                if (!count($footer_menu)) {
                    $footer_menu = Page::query()
                        ->public()
                        ->where('on_footer', 1)
                        ->orderBy('order')
                        ->get();
                    Cache::add('footer_menu', $footer_menu, now()->addMinutes(60));
                }

                if (!$city_alias = session('city_alias')) {
                    $current_city = null;
                } else {
                    $current_city = City::whereAlias($city_alias)->first();
                }

                $view->with(
                    compact(
                        [
                            'header_menu',
                            'mobile_menu',
                            'footer_menu',
                            'current_city'
                        ]
                    )
                );
            }
        );
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'settings',
            function () {
                return new \App\Classes\Settings();
            }
        );
        $this->app->bind(
            'sitehelper',
            function () {
                return new \App\Classes\SiteHelper();
            }
        );
        $this->app->alias('settings', \App\Facades\Settings::class);
        $this->app->alias('sitehelper', \App\Facades\SiteHelper::class);
    }
}
