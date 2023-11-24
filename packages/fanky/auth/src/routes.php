<?php

Route::any('auth', ['uses' => 'Fanky\Auth\Controllers\AuthController@index', 'as' => 'auth']);
