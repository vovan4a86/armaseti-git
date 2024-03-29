<?php

use App\Http\Controllers\AjaxController;

Route::get('robots.txt', 'PageController@robots')->name('robots');

Route::group(
    ['prefix' => 'ajax', 'as' => 'ajax.'],
    function () {
        Route::post('add-to-cart', [AjaxController::class, 'postAddToCart'])->name('add-to-cart');
        Route::post('update-to-cart', [AjaxController::class, 'postUpdateToCart'])->name('update-to-cart');
        Route::post('remove-from-cart', [AjaxController::class, 'postRemoveFromCart'])->name('remove-from-cart');
        Route::post('restore-from-cart', [AjaxController::class, 'postRestoreFromCart'])->name('restore-from-cart');
        Route::post('update-count', [AjaxController::class, 'postUpdateCount'])->name('update-count');
        Route::post('purge-cart', [AjaxController::class, 'postPurgeCart'])->name('purge-cart');
        Route::post('edit-cart-product', [AjaxController::class, 'postEditCartProduct'])->name('edit-cart-product');

        Route::post('make-order', 'AjaxController@postMakeOrder')->name('make-order');
        Route::post('send-request', 'AjaxController@postSendRequest')->name('send-request');

        Route::get('show-popup-cities', [AjaxController::class, 'showCitiesPopup'])->name('show-popup-cities');
        Route::post('set-city', 'AjaxController@postSetCity')->name('set-city');
        Route::post('set-default-city', 'AjaxController@postSetDefaultCity')->name('set-default-city');
        Route::post('get-correct-region-link', 'AjaxController@postGetCorrectRegionLink')->name(
            'get-correct-region-link'
        );

        Route::post('update-catalog-filter', 'AjaxController@postUpdateCatalogFilter')
            ->name('update-catalog-filter');

        Route::post('compare', 'AjaxController@postCompare')->name('compare');
        Route::post('compare-delete', 'AjaxController@postCompareDelete')->name('compare-delete');
        Route::post('favorites', 'AjaxController@postFavorites')->name('favorites');

//        Route::post('apply-filter/{category_id}', 'AjaxController@postApplyFilter')->name('apply-filter');
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

        Route::any('contacts', ['as' => 'contacts', 'uses' => 'ContactsController@index']);

        Route::get('cart', ['as' => 'cart', 'uses' => 'CartController@index']);
        Route::get('order-success', ['as' => 'order-success', 'uses' => 'CartController@success']);

        Route::any('policy', ['as' => 'policy', 'uses' => 'PageController@policy']);

        Route::any('catalog', ['as' => 'catalog', 'uses' => 'CatalogController@index']);
        Route::any('catalog/compare', ['as' => 'catalog.compare', 'uses' => 'CatalogController@compare']);
        Route::any('catalog/{alias}', ['as' => 'catalog.view', 'uses' => 'CatalogController@view'])
            ->where('alias', '([A-Za-z0-9\-\/_]+)');

        Route::any('new-products', ['as' => 'new-products', 'uses' => 'CatalogController@new']);

        Route::any('search', ['as' => 'search', 'uses' => 'CatalogController@search']);

        Route::any('compare', ['as' => 'compare', 'uses' => 'CatalogController@compare']);
        Route::any('favorites', ['as' => 'favorites', 'uses' => 'CatalogController@favorites']);

        Route::any('{alias}', ['as' => 'default', 'uses' => 'PageController@page'])
            ->where('alias', '([A-Za-z0-9\-\/_]+)');
    }
);
