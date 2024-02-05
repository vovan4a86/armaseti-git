<?php namespace App\Providers;

use Fanky\Admin\Models\AdminLog;
use Fanky\Admin\Models\Athlete;
use Fanky\Admin\Models\AthleteTeam;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\Discipline;
use Fanky\Admin\Models\Complex;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\NewsCategory;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\Sport;
use Fanky\Admin\Models\Team;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AdminLogServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {

		Page::created(function($obj){
			AdminLog::add('Создана новая страница: ' . $obj->name);
		});

		Page::updated(function($obj){
			AdminLog::add('Отредактирована страница: ' . $obj->name);
            if ($obj->isDirty('published')) {
                Cache::flush();
            }
		});

		Page::deleting(function($obj){
			AdminLog::add('Удалена страница: ' . $obj->name);
		});

		Catalog::created(function($obj){
			AdminLog::add('Создана новая категория: ' . $obj->name);
		});

		Catalog::updated(function($obj){
			AdminLog::add('Отредактирована категория: ' . $obj->name);
            if ($obj->isDirty(['published', 'on_main'])) {
                Cache::flush();
            }
		});

		Catalog::deleting(function($obj){
			AdminLog::add('Удалена категория: ' . $obj->name);
		});

        News::created(function($obj){
            AdminLog::add('Создана новость: ' . $obj->name);
        });

        News::updated(function($obj){
            AdminLog::add('Отредактирована новость: ' . $obj->name);
        });

        News::deleting(function($obj){
            AdminLog::add('Удалена новость: ' . $obj->name);
        });

		Product::created(function($obj){
			AdminLog::add('Создан новый товар: ' . $obj->name);
		});

		Product::updated(function($obj){
			AdminLog::add('Отредактирован товар: ' . $obj->name);
		});

		Product::deleting(function($obj){
			AdminLog::add('Удален товар: ' . $obj->name);
		});
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
	public function register() {
	}

}
