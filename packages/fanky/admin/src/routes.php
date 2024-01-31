<?php

//Route::any('admin', ['as' => 'admin', 'uses' => 'Fanky\Admin\Controllers\AdminController@main']);
use Fanky\Admin\Controllers\AdminCatalogController;

Route::group(['namespace' => 'Fanky\Admin\Controllers', 'prefix' => 'admin', 'as' => 'admin'], function () {
	Route::any('/', ['uses' => 'AdminController@main']);
	Route::group(['as' => '.pages', 'prefix' => 'pages'], function () {
		$controller  = 'AdminPagesController@';
		Route::get('/', $controller . 'getIndex');
		Route::get('edit/{id?}', $controller . 'getEdit')
			->name('.edit');

		Route::post('edit/{id?}', $controller . 'postEdit')
			->name('.edit');

		Route::get('get-pages/{id?}', $controller . 'getGetPages')
			->name('.get_pages');

		Route::post('save', $controller . 'postSave')
			->name('.save');

		Route::post('reorder', $controller . 'postReorder')
			->name('.reorder');

		Route::post('delete/{id}', $controller . 'postDelete')
			->name('.del');

		Route::post('top-view-del/{id}', $controller . 'postTopViewDel')
			->name('.top-view-del');

		Route::get('filemanager', [
			'as'   => '.filemanager',
			'uses' => $controller . 'getFileManager'
		]);

		Route::get('imagemanager', [
			'as'   => '.imagemanager',
			'uses' => $controller . 'getImageManager'
		]);

        Route::post('add-gost-file/{id}', [
            'as'   => '.addGostFile',
            'uses' => $controller . 'postAddGostFile'
        ]);

        Route::post('del-gost-file/{id}', [
            'as'   => '.delGostFile',
            'uses' => $controller . 'postDelGostFile'
        ]);

        Route::post('update-gost-file-order/{id}', [
            'as'   => '.updateGostFileOrder',
            'uses' => $controller . 'postUpdateGostFileOrder'
        ]);
	});

	Route::group(['as' => '.catalog', 'prefix' => 'catalog'], function () {
		$controller  = 'AdminCatalogController@';
		Route::get('/', [AdminCatalogController::class, 'getIndex']);

        Route::get('get-catalogs/{id?}', $controller . 'getGetCatalogs')
            ->name('.get_catalogs');

		Route::get('products/{id?}', $controller . 'getProducts')
			->name('.products');

		Route::post('catalog-edit/{id?}', $controller . 'postCatalogEdit')
			->name('.catalogEdit');

		Route::get('catalog-edit/{id?}', $controller . 'getCatalogEdit')
			->name('.catalogEdit');

		Route::post('catalog-save', $controller . 'postCatalogSave')
			->name('.catalogSave');

		Route::post('catalog-reorder', $controller . 'postCatalogReorder')
			->name('.catalogReorder');

		Route::post('catalog-delete/{id}', $controller . 'postCatalogDelete')
			->name('.catalogDel');

        Route::post('catalog-image-delete/{id}', $controller . 'postCatalogImageDelete')
            ->name('.catalogImageDel');

        Route::post('catalog-icon-delete/{id}', $controller . 'postCatalogIconDelete')
            ->name('.catalogIconDel');

        Route::post('catalog-filter-update-order', $controller . 'postCatalogFilterUpdateOrder')
            ->name('.catalog-filter-update-order');

        Route::post('catalog-filter-edit/{id}', $controller . 'postCatalogFilterEdit')
            ->name('.catalog-filter-edit');

        Route::post('catalog-filter-delete/{id}', $controller . 'postCatalogFilterDelete')
            ->name('.catalog-filter-delete');

        Route::post('catalog-filter-save-data/{id}', $controller . 'postCatalogFilterSaveData')
            ->name('.catalog-filter-save-data');

        Route::post('catalog-gallery-image-upload/{id}', $controller . 'postCatalogGalleryImageUpload')
            ->name('.catalogGalleryImageUpload');

        Route::post('catalog-gallery-image-delete/{id}', $controller . 'postCatalogGalleryImageDelete')
            ->name('.catalogGalleryImageDelete');

        Route::post('catalog-gallery-image-order', $controller . 'postCatalogGalleryImageOrder')
            ->name('.catalogGalleryImageOrder');

        Route::post('product-image-delete/{id}', $controller . 'postProductImageDelete')
            ->name('.productImageDel');

        Route::post('product-image-order', $controller . 'postProductImageOrder')
            ->name('.productImageOrder');

		Route::get('product-edit/{id?}', $controller . 'getProductEdit')
			->name('.productEdit');

		Route::post('product-save', $controller . 'postProductSave')
			->name('.productSave');

		Route::post('product-reorder', $controller . 'postProductReorder')
			->name('.productReorder');

		Route::post('update-order/{id}', $controller . 'postUpdateOrder')
			->name('.update-order');

		Route::post('product-delete/{id}', $controller . 'postProductDelete')
			->name('.productDel');

		Route::post('product-image-upload/{id}', $controller . 'postProductImageUpload')
			->name('.productImageUpload');

		Route::post('product-image-delete/{id}', $controller . 'postProductImageDelete')
			->name('.productImageDel');

		Route::post('product-image-order', $controller . 'postProductImageOrder')
			->name('.productImageOrder');

        Route::post('product-delete-char/{id}', $controller . 'postProductDeleteChar')
            ->name('.product-delete-char');

        Route::post('product-update-order-char', $controller . 'postProductUpdateOrderChar')
            ->name('.product-update-order-char');

        //product doc
        Route::post('product-add-doc/{id}', $controller . 'postProductAddDoc')
            ->name('.product-add-doc');

        Route::post('product-del-doc/{id}', $controller . 'postProductDelDoc')
            ->name('.product-del-doc');

        Route::post('product-edit-doc/{id}', $controller . 'postProductEditDoc')
            ->name('.product-edit-doc');

        Route::post('product-save-doc/{id}', $controller . 'postProductSaveDoc')
            ->name('.product-save-doc');

        Route::post('product-update-order-doc', $controller . 'postProductUpdateOrderDoc')
            ->name('.product-update-order-doc');

        //catalog doc
        Route::post('catalog-add-doc/{id}', $controller . 'postCatalogAddDoc')
            ->name('.catalog-add-doc');

        Route::post('catalog-del-doc/{id}', $controller . 'postCatalogDelDoc')
            ->name('.catalog-del-doc');

        Route::post('catalog-edit-doc/{id}', $controller . 'postCatalogEditDoc')
            ->name('.catalog-edit-doc');

        Route::post('catalog-save-doc/{id}', $controller . 'postCatalogSaveDoc')
            ->name('.catalog-save-doc');

        Route::post('catalog-update-order-doc', $controller . 'postCatalogUpdateOrderDoc')
            ->name('.catalog-update-order-doc');

        //search
        Route::get('search', $controller . 'search')
            ->name('.search');

        //mass
        Route::post('move-products', [
            'as' => '.move-products',
            'uses' => $controller . 'postMoveProducts'
        ]);

        Route::post('delete-products', [
            'as' => '.delete-products',
            'uses' => $controller . 'postDeleteProducts'
        ]);

        Route::post('delete-products-image', [
            'as' => '.delete-products-image',
            'uses' => $controller . 'postDeleteProductsImage'
        ]);

        //toggle products checkbox
        Route::post('product-toggle-is-new/{id}', $controller . 'postProductToggleIsNew')
            ->name('.product-toggle-is-new');

        Route::post('product-toggle-is-hit/{id}', $controller . 'postProductToggleIsHit')
            ->name('.product-toggle-is-hit');

	});

    Route::group(['as' => '.news', 'prefix' => 'news'], function () {
        $controller = 'AdminNewsController@';
        Route::get('/', $controller . 'getIndex');

        Route::get('edit/{id?}', $controller . 'getEdit')
            ->name('.edit');

        Route::post('save', $controller . 'postSave')
            ->name('.save');

        Route::post('delete/{id}', $controller . 'postDelete')
            ->name('.delete');

        Route::post('delete-image/{id}', $controller . 'postDeleteImage')
            ->name('.delete-image');

        Route::post('news-image-upload/{id}', $controller . 'postNewsImageUpload')
            ->name('.newsImageUpload');

        Route::post('news-image-delete/{id}', $controller . 'postNewsImageDelete')
            ->name('.newsImageDel');

        Route::post('news-image-order', $controller . 'postNewsImageOrder')
            ->name('.newsImageOrder');
    });

    Route::group(['as' => '.contacts', 'prefix' => 'contacts'], function () {
        $controller = 'AdminContactsController@';
        Route::get('/', $controller . 'getIndex');

        Route::get('edit/{id?}', $controller . 'getEdit')
            ->name('.edit');

        Route::post('save', $controller . 'postSave')
            ->name('.save');

        Route::post('delete/{id}', $controller . 'postDelete')
            ->name('.delete');

        Route::post('update-order/{id}', $controller . 'postUpdateOrder')
            ->name('.update-order');
    });

    Route::group(['as' => '.orders', 'prefix' => 'orders'], function () {
		$controller = 'AdminOrdersController@';
		Route::get('/', $controller . 'getIndex');

		Route::get('view/{id?}', $controller . 'getView')
			->name('.view');

		Route::post('del/{id}', $controller . 'postDelete')
			->name('.del');
	});

	Route::group(['as' => '.gallery', 'prefix' => 'gallery'], function () {
		$controller = 'AdminGalleryController@';
		Route::get('/', $controller . 'anyIndex');
		Route::post('gallery-save', $controller . 'postGallerySave')
			->name('.gallerySave');
		Route::post('gallery-edit/{id?}', $controller . 'postGalleryEdit')
			->name('.gallery_edit');
		Route::post('gallery-delete/{id}', $controller . 'postGalleryDelete')
			->name('.galleryDel');
		Route::any('items/{id}', $controller . 'anyItems')
			->name('.items');
		Route::post('image-upload/{id}', $controller . 'postImageUpload')
			->name('.imageUpload');
		Route::post('image-edit/{id}', $controller . 'postImageEdit')
			->name('.imageEdit');
		Route::post('image-data-save/{id}', $controller . 'postImageDataSave')
			->name('.imageDataSave');
		Route::post('image-del/{id}', $controller . 'postImageDelete')
			->name('.imageDel');
		Route::post('image-order', $controller . 'postImageOrder')
			->name('.order');
	});

	Route::group(['as' => '.reviews', 'prefix' => 'reviews'], function () {
		$controller = 'AdminReviewsController@';
		Route::get('/', $controller . 'getIndex');

		Route::get('edit/{id?}', $controller . 'getEdit')
			->name('.edit');

		Route::post('save', $controller . 'postSave')
			->name('.save');

		Route::post('reorder', $controller . 'postReorder')
			->name('.reorder');

		Route::post('delete/{id}', $controller . 'postDelete')
			->name('.del');

        Route::post('delete-image/{id}', $controller . 'postDeleteImage')
            ->name('.delImage');
	});

    Route::group(['as' => '.customers', 'prefix' => 'customers'], function () {
        $controller = 'AdminCustomersController@';
        Route::get('/', $controller . 'getIndex');

        Route::get('edit/{id?}', $controller . 'getEdit')
            ->name('.edit');

        Route::post('save', $controller . 'postSave')
            ->name('.save');

        Route::post('delete/{id}', $controller . 'postDelete')
            ->name('.delete');

        Route::post('favorites', $controller . 'postGetFavorites')
            ->name('.favorites');

        Route::post('add-to-favorite', $controller . 'postAddToFavorite')
            ->name('.add-to-favorite');
    });

    Route::group(['as' => '.feedbacks', 'prefix' => 'feedbacks'], function () {
		$controller = 'AdminFeedbacksController@';
		Route::get('/', $controller . 'getIndex');

		Route::post('read/{id?}',$controller . 'postRead')
			->name('.read');
		Route::post('delete/{id?}', $controller . 'postDelete')
			->name('.del');
	});

    Route::group(['as' => '.cities', 'prefix' => 'cities'], function () {
		$controller = 'AdminCitiesController@';
		Route::get('/', $controller . 'getIndex');

		Route::get('edit/{id?}', $controller . 'getEdit')
			->name('.edit');

		Route::post('delete/{id}', $controller . 'postDelete')
			->name('.del');

		Route::post('save', $controller . 'postSave')
			->name('.save');

		Route::post('tree/{id?}', $controller . 'postTree')
			->name('.tree');
	});

    Route::group(['as' => '.settings', 'prefix' => 'settings', 'middleware' => ['admin.fanky']], function () {
        $controller = 'AdminSettingsController@';
        Route::get('/', $controller . 'getIndex');

        Route::get('group-items/{id?}', $controller . 'getGroupItems')
            ->name('.groupItems');

        Route::post('group-save', $controller . 'postGroupSave')
            ->name('.groupSave');

        Route::post('group-delete/{id}', $controller . 'postGroupDelete')
            ->name('.groupDel');

        Route::post('clear-value/{id}', $controller . 'postClearValue')
            ->name('.clearValue');

        Route::any('edit/{id?}', $controller . 'anyEditSetting')
            ->name('.edit');

        Route::any('block-params', $controller . 'anyBlockParams')
            ->name('.blockParams');

        Route::post('edit-setting-save', $controller . 'postEditSettingSave')
            ->name('.editSave');

        Route::post('save', $controller . 'postSave')
            ->name('.save');
    });

    Route::group(['as' => '.redirects', 'prefix' => 'redirects', 'middleware' => ['admin.fanky']], function () {
        $controller = 'AdminRedirectsController@';
        Route::get('/', $controller . 'getIndex');

        Route::get('edit/{id?}', $controller . 'getEdit')
            ->name('.edit');

        Route::get('delete/{id}', $controller . 'getDelete')
            ->name('.delete');

        Route::post('save', $controller . 'postSave')
            ->name('.save');
    });

    Route::group(['as' => '.users', 'prefix' => 'users', 'middleware' => ['admin.fanky']], function () {
        $controller = 'AdminUsersController@';
        Route::get('/', $controller . 'getIndex');

        Route::post('edit/{id?}', $controller . 'postEdit')
            ->name('.edit');

        Route::post('save', $controller . 'postSave')
            ->name('.save');

        Route::post('del/{id}', $controller . 'postDelete')
            ->name('.del');
    });
});
