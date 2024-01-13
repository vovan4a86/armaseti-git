<?php

use App\Http\Controllers\AjaxController;

Route::get('robots.txt', 'PageController@robots')->name('robots');

Route::group(
    ['prefix' => 'ajax', 'as' => 'ajax.'],
    function () {
        Route::post('add-to-cart', [AjaxController::class, 'postAddToCart'])->name('add-to-cart');
        Route::post('update-to-cart', [AjaxController::class, 'postUpdateToCart'])->name('update-to-cart');
        Route::post('remove-from-cart', [AjaxController::class, 'postRemoveFromCart'])->name('remove-from-cart');
        Route::post('purge-cart', [AjaxController::class, 'postPurgeCart'])->name('purge-cart');
        Route::post('edit-cart-product', [AjaxController::class, 'postEditCartProduct'])->name('edit-cart-product');

        Route::post('calc', 'AjaxController@postCalc')->name('calc');
        Route::post('callback', 'AjaxController@postCallback')->name('callback');
        Route::post('feedback', 'AjaxController@postFeedback')->name('feedback');
        Route::post('request-price', 'AjaxController@postRequestPrice')->name('request-price');

        Route::get('show-popup-cities', [AjaxController::class, 'showCitiesPopup'])->name('show-popup-cities');
        Route::post('set-city', 'AjaxController@postSetCity')->name('set-city');
        Route::post('get-correct-region-link', 'AjaxController@postGetCorrectRegionLink')->name(
            'get-correct-region-link'
        );

        Route::post('update-catalog-filter', 'AjaxController@postUpdateCatalogFilter')
            ->name('update-catalog-filter');

        Route::post('compare', 'AjaxController@postCompare')->name('compare');
        Route::post('compare-delete', 'AjaxController@postCompareDelete')->name('compare-delete');
        Route::post('favorite', 'AjaxController@postFavorite')->name('favorite');

        Route::post('apply-filter/{category_id}', 'AjaxController@postApplyFilter')->name('apply-filter');
    }
);

Route::group(
    ['middleware' => ['redirects', 'regions']],
    function () {
        $cities = getCityAliases();
        $cities = implode('|', $cities);
        Route::group(
            [
                'prefix' => '{city}',
                'as' => 'region.',
                'where' => ['city' => $cities]
            ],
            function () use ($cities) {
                Route::get('/', ['as' => 'index', 'uses' => 'PageController@page']);
                Route::group(
                    ['prefix' => 'catalog', 'as' => 'catalog.'],
                    function () {
                        Route::any('/', ['as' => 'index', 'uses' => 'CatalogController@index']);
                        Route::any('{alias}', ['as' => 'view', 'uses' => 'CatalogController@view'])
                            ->where('alias', '([A-Za-z0-9\-\/_]+)');
                    }
                );

                Route::any('{alias}', ['as' => 'pages', 'uses' => 'PageController@region_page'])
                    ->where('alias', '([A-Za-z0-9\-\/_]+)');
            }
        );

        Route::get('/', ['as' => 'main', 'uses' => 'WelcomeController@index']);

        Route::any('news', ['as' => 'news', 'uses' => 'NewsController@index']);
        Route::any('news/{alias}', ['as' => 'news.item', 'uses' => 'NewsController@item']);

        Route::any('handbook', ['as' => 'handbook', 'uses' => 'HandbookController@index']);
        Route::any('handbook/{alias}', ['as' => 'handbook.item', 'uses' => 'HandbookController@item']);

        Route::any('reviews', ['as' => 'reviews', 'uses' => 'ReviewsController@index']);
        Route::any('reviews/{alias}', ['as' => 'reviews.item', 'uses' => 'ReviewsController@item']);

        Route::any('contacts', ['as' => 'contacts', 'uses' => 'ContactsController@index']);

        Route::any('search', ['as' => 'search', 'uses' => 'CatalogController@search']);

        Route::get('cart', ['as' => 'cart', 'uses' => 'CartController@index']);

        Route::any('policy', ['as' => 'policy', 'uses' => 'PageController@policy']);

        Route::any('catalog', ['as' => 'catalog', 'uses' => 'CatalogController@index']);
        Route::any('catalog/compare', ['as' => 'catalog.compare', 'uses' => 'CatalogController@compare']);

        Route::any('catalog/{alias}', ['as' => 'catalog.view', 'uses' => 'CatalogController@view'])
            ->where('alias', '([A-Za-z0-9\-\/_]+)');

        Route::any('{alias}', ['as' => 'default', 'uses' => 'PageController@page'])
            ->where('alias', '([A-Za-z0-9\-\/_]+)');
    }
);
