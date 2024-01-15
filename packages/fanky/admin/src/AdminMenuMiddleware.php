<?php namespace Fanky\Admin;

use Auth;
use Closure;
use Menu;

class AdminMenuMiddleware {

	/**
	 * Run the request filter.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$cur_user = Auth::user();
		Menu::make('main_menu', function (\Lavary\Menu\Builder $menu) use($cur_user, $request) {
			$menu->add('Структура сайта', ['route' => 'admin.pages', 'icon' => 'fa-sitemap'])
				->active('/admin/pages/*');

			$menu->add('Каталог', ['route' => 'admin.catalog', 'icon' => 'fa-list'])
				->active('/admin/catalog/*');

            $menu->add('Новости', ['route' => 'admin.news', 'icon' => 'fa-calendar'])
                ->active('/admin/news/*');

            $menu->add('Заказы', ['route' => 'admin.orders', 'icon' => 'fa-dollar'])
				->active('/admin/orders/*');

//            $menu->add('Отзывы', ['route' => 'admin.reviews', 'icon' => 'fa-star'])
//                ->active('/admin/reviews/*');

            $menu->add('Региональность', ['route' => 'admin.cities', 'icon' => 'fa-globe'])
				->active('/admin/cities/*');

//			$menu->add('Галереи', ['route' => 'admin.gallery', 'icon' => 'fa-image'])
//				->active('/admin/gallery/*');

			$menu->add('Настройки', ['icon' => 'fa-cogs'])
				->nickname('settings');
			$menu->settings->add('Настройки', ['route' => 'admin.settings', 'icon' => 'fa-gear'])
				->active('/admin/settings/*');
//            $menu->settings->add('Характеристики продуктов', ['route' => 'admin.char_settings', 'icon' => 'fa-gear'])
//                ->active('/admin/chars_settings/*');
			$menu->settings->add('Редиректы', ['route' => 'admin.redirects', 'icon' => 'fa-retweet'])
				->active('/admin/redirects/*');
//			$menu->add('Медиаменеджер', ['route' => 'admin.pages.imagemanager', 'icon' => 'fa-picture-o'])
//				->active('/admin/pages/imagemanager');
			$menu->add('Файловый менеджер', ['route' => 'admin.pages.filemanager', 'icon' => 'fa-file'])
				->active('/admin/pages/filemanager');
		});

		return $next($request);
	}

}
