<?php
/**
 * Created by PhpStorm.
 * User: Андрей
 * Date: 06.11.2015
 * Time: 12:32
 */

namespace App\Classes;

use App;
use Carbon\Carbon;
use Fanky\Admin\Models\Catalog as Catalog;
use Fanky\Admin\Models\City;
use Cache;
use Fanky\Admin\Models\News;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Redirect;
use Fanky\Admin\Models\Product;
use App\Sitemap as Sitemap;
use Fanky\Admin\Models\SearchIndex;
use Image;
use Request;

class SiteHelper {

	public static $monthRu = [
		'January'   => 'Января',
		'February'  => 'Февраля',
		'March'     => 'Марта',
		'April'     => 'Апреля',
		'May'       => 'Мая',
		'June'      => 'Июня',
		'July'      => 'Июля',
		'August'    => 'Августа',
		'September' => 'Сентября',
		'October'   => 'Октября',
		'November'  => 'Ноября',
		'December'  => 'Декабря'
	];

	public static $monthRu2 = [
		'January'   => 'Январь',
		'February'  => 'Февраль',
		'March'     => 'Март',
		'April'     => 'Апрель',
		'May'       => 'Май',
		'June'      => 'Июнь',
		'July'      => 'Июль',
		'August'    => 'Август',
		'September' => 'Сентябрь',
		'October'   => 'Октябрь',
		'November'  => 'Ноябрь',
		'December'  => 'Декабрь'
	];
	public static $weekdayRu = [
		'Monday'    => 'Понедельник',
		'Tuesday'   => 'Вторник',
		'Wednesday' => 'Среда',
		'Thursday'  => 'Четверг',
		'Friday'    => 'Пятница',
		'Saturday'  => 'Суббота',
		'Sunday'    => 'Воскресенье',
	];
	public static $weekdayRuByNum = [
		1 => 'Понедельник',
		2 => 'Вторник',
		3 => 'Среда',
		4 => 'Четверг',
		5 => 'Пятница',
		6 => 'Суббота',
		7 => 'Воскресенье',
	];

	/**
	 * Функция возвращает окончание для множественного числа слова на основании числа и массива окончаний
	 *
	 * @param  $number       Integer Число на основе которого нужно сформировать окончание
	 * @param  $endingArray  Array Массив слов или окончаний для чисел (1, 4, 5),
	 *                       например array('яблоко', 'яблока', 'яблок')
	 *
	 * @return String
	 */
	public static function getNumEnding($number, $endingArray) {
		$number = $number % 100;
		if ($number >= 11 && $number <= 19) {
			$ending = $endingArray[2];
		} else {
			$i = $number % 10;
			switch ($i) {
				case (1):
					$ending = $endingArray[0];
					break;
				case (2):
				case (3):
				case (4):
					$ending = $endingArray[1];
					break;
				default:
					$ending = $endingArray[2];
			}
		}

		return $ending;
	}

	public static function getRedirects($from = null) {
		$redirects = Cache::get('redirects', []);
		if (!$redirects) {
			$redirects_arr = Redirect::all(['from', 'to', 'code']);
			foreach ($redirects_arr as $item) {
				$redirects[$item->from] = $item;
			}
			Cache::add('redirects', $redirects, 60);
		}
		if (!is_null($from)) {
			return isset($redirects[$from]) ? $redirects[$from] : null;
		} else {
			return $redirects;
		}
	}

	/**
	 * @param Sitemap $map
	 * @param Catalog $parent
	 */
	private static function recurseAddCatalog(&$map, $parent = null) {
		if ($parent) {
			$parent_url = $parent->url;
			$catalogs = $parent->getPublicChildren();
		} else {
			$parent_url = Page::getByPath(['catalog'])->url;
			$catalogs = Catalog::whereParentId(0)->public()->get();
		}
		foreach ($catalogs as $catalog) {
			$catalog_url = $parent_url . '/' . $catalog->alias;
			$map->add_url($catalog_url);
			self::$urls[] = $catalog_url;
			$products = $catalog->products()->public()->get();
			foreach ($products as $product) {
				$map->add_url($catalog_url . '/' . $product->id);
				self::$urls[] = $catalog_url . '/' . $product->id;
			}
			self::recurseAddCatalog($map, $catalog);
		}
	}

	/**
	 * @param Sitemap $map
	 * @param Page    $parent
	 */
	public static function recurseAddPages(&$map, $parent = null) {
		if ($parent) {
			$parent_url = $parent->url;
			$pages = $parent->getPublicChildren();
		} else {
			$parent_url = url('/');
			$pages = Page::whereId(1)->get();
		}

		foreach ($pages as $page) {
			if($page->id == 1){
				$url = url('/');
			} else {
				$url = $parent_url . '/' . $page->alias;
			}

			$map->add_url($url);
			if(!in_array($page->alias, Page::$excludeRegionAlias)){
				self::$urls[] = $url;
			}

			self::recurseAddPages($map, $page);
		}
	}

    public static function generateSitemap() {
		array_map("unlink", glob(public_path('/sitemaps/*.xml')));
		self::generateFederalSitemap();
//		self::generateRegionSitemap();
//		self::generateSitemapIndex();
	}

	private static $urls = [];

	private static function generateFederalSitemap() {
		session(['city_alias' => '']);
		$map = new Sitemap('');

		//страницы
		self::recurseAddPages($map);

		//разделы каталога
		self::recurseAddCatalog($map);

		//товары
		$products = Product::wherePublished(1)->get();
		foreach ($products as $item) {
			$map->add_url($item->url);
		}

		//Новости
		$items = News::wherePublished(1)->get();
		foreach ($items as $item) {
			$map->add_url($item->url);
		}

//		$map->save('sitemaps/federal.xml');
		$map->save('sitemap.xml');
	}

	private static function generateRegionSitemap() {
		$cities = City::query()->get();
		foreach ($cities as $city) {
			session(['city_alias' => $city->alias]);
			$map = new Sitemap('');
			$map->add_url(url($city->alias));
			$urls = self::getRegionLinkFromFederal($city, self::$urls);
			foreach ($urls as $url) {
				$map->add_url($url);
			}

			$map->save('sitemaps/' . $city->alias . '.xml');
		}
	}

	private static function generateSitemapIndex() {
		$xml = new \SimpleXMLElement('<sitemapindex />');
		$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		foreach (glob(public_path('/sitemaps/*.xml')) as $filename) {
			$sitemap = $xml->addChild('sitemap');
			$p_info = pathinfo($filename);
			//YYYY-MM-DDThh:mm:ss±hh:mm
			$timestamp = \File::lastModified($filename);
			$lastmodify = date('c', $timestamp);
			$sitemap->addChild('loc', \URL::to('sitemaps/' . array_get($p_info, 'basename')));
			$sitemap->addChild('lastmod', $lastmodify);
		}
		$xml->asXML(public_path('sitemap.xml'));
	}


	public static $category_ids = [];
	public static $offers = [];
	/**
	 * @param int $parent_id
	 * @param \DOMElement $categories
	 * @param \DOMDocument $doc
	 */
	public static function recurseXmlAddCatalog($parent_id, &$categories, &$doc, $with_offers = true) {
		$cats = Catalog::whereParentId($parent_id)->public()->orderBy('order')->get();
		foreach ($cats as $cat){
			$category = $doc->createElement('category', $cat->name);
			$category->setAttribute('id', $cat->id);
			if ($cat->parent_id) {
				$category->setAttribute('parentId', $cat->parent_id);
			}
			$categories->appendChild($category);
			self::$category_ids[] = $cat->id;
			if($with_offers){
				self::getOffers($cat, $doc);
			}
			self::recurseXmlAddCatalog($cat->id, $categories, $doc);
		}
	}

	private static function getOffers(Catalog $category, &$doc){
		$category_url = $category->url;
		$products = $category->products()->public()->where('price', '>', 0)->get();
		/** @var Product[] $products */
		foreach ($products as $product) {
			$offer = $doc->createElement('offer');
			$offer->setAttribute('id', $product->id);
			//$offer->setAttribute('type', 'vendor.model');
			$offer->setAttribute('available', 'true');

			$url = $doc->createElement('url', $category_url . '/' . $product->id);
			$price = $doc->createElement('price', $product->price);

			$currency = $doc->createElement('currencyId', 'RUR');
			$category = $doc->createElement('categoryId', $product->catalog_id);
			//$vendor = $doc->createElement('vendor', 'Russia');
			$name = $doc->createElement('name', htmlspecialchars($product->name));
			$product->generateDescription();
			$descr = $product->description;
			$descr = str_replace('{city}', ' в Екатеринбурге', $descr);
			$description = $doc->createElement('description', htmlspecialchars(strip_tags($descr)));

			$offer->appendChild($url);
			$offer->appendChild($price);
			$offer->appendChild($currency);
			$offer->appendChild($category);
			if ($image = $product->image) {
				$image = $doc->createElement('picture', $image->src);
				$offer->appendChild($image);
			}
			$offer->appendChild($name);
			if($description) $offer->appendChild($description);
			$params = [
				'size'	=> 'Толщина/размер (мм)',
				'steel'	=> 'Марка стали',
				'length'	=> 'Длина (м)',
				'gost'	=> 'ГОСТ',
			];
			foreach ($params as $field => $name){
				if($product->{$field}){
					$param = $doc->createElement('param', $product->{$field});
					$param->setAttribute('name', $name);
					$offer->appendChild($param);
				}
			}
			self::$offers[] = $offer;
		}
	}

	public static function generateYandexXml() {
		try{
			$implementation = new \DOMImplementation();
			$dtd = $implementation->createDocumentType('yml_catalog', null, 'shops.dtd');

			$doc = $implementation->createDocument('', '', $dtd);
			$doc->encoding = 'UTF-8';
			$yml_catalog = $doc->createElement('yml_catalog');
			$yml_catalog = $doc->appendChild($yml_catalog);
			$yml_catalog->setAttribute('date', date('Y-m-d H:i'));
			$shop = $doc->createElement('shop');
			$yml_catalog->appendChild($shop);
			$name = $doc->createElement('name', 'td-artstal');
			$company = $doc->createElement('company', 'ТД Арт');
			$url = $doc->createElement('url', url('/'));
			$shop->appendChild($name);
			$shop->appendChild($company);
			$shop->appendChild($url);
			$currencies = $doc->createElement('currencies');
			$currency = $doc->createElement('currency');
			$currency->setAttribute('id', 'RUR');
			$currency->setAttribute('rate', '1');
			$currencies->appendChild($currency);
			$shop->appendChild($currencies);
			$categories = $doc->createElement('categories');
//			$cats = Catalog::wherePublished(1)->get();
			self::recurseXmlAddCatalog(0, $categories, $doc);

			$shop->appendChild($categories);

			$offers = $doc->createElement('offers');
			foreach (self::$offers as $offer){
				$offers->appendChild($offer);
			}
			$shop->appendChild($offers);

			$path = public_path( 'yandex.xml');
			$fh = fopen($path, 'w+');
			fwrite($fh, $doc->saveXML());
			fclose($fh);

			return true;
		} catch (\Exception $e){
			echo $e->getMessage() . "\n" . $e->getLine();
			return false;
		}
	}

	public static function generateSmallYandexXml() {
		try{
			$implementation = new \DOMImplementation();
			$dtd = $implementation->createDocumentType('yml_catalog', null, 'shops.dtd');

			$doc = $implementation->createDocument('', '', $dtd);
			$doc->encoding = 'UTF-8';
			$yml_catalog = $doc->createElement('yml_catalog');
			$yml_catalog = $doc->appendChild($yml_catalog);
			$yml_catalog->setAttribute('date', date('Y-m-d H:i'));
			$shop = $doc->createElement('shop');
			$yml_catalog->appendChild($shop);
			$name = $doc->createElement('name', 'td-artstal');
			$company = $doc->createElement('company', 'ТД Арт');
			$url = $doc->createElement('url', url('/'));
			$shop->appendChild($name);
			$shop->appendChild($company);
			$shop->appendChild($url);
			$currencies = $doc->createElement('currencies');
			$currency = $doc->createElement('currency');
			$currency->setAttribute('id', 'RUR');
			$currency->setAttribute('rate', '1');
			$currencies->appendChild($currency);
			$shop->appendChild($currencies);
			$categories = $doc->createElement('categories');
//			$cats = Catalog::wherePublished(1)->get();
			self::recurseXmlAddCatalog(0, $categories, $doc, false);

			$shop->appendChild($categories);

			$offers = $doc->createElement('offers');
			//полоса оцинков
			$products1 = Product::whereCatalogId(104)->public()->limit(100)->get()->keyBy('id');
			//круг оцинков
			$products2 = Product::whereCatalogId(111)->public()->limit(100)->get()->keyBy('id');
			//труба бесшовная
			$products3 = Product::whereCatalogId(71)->public()->where('wall', '>=', 4)->limit(100)->get()->keyBy('id');
			$products = $products1->merge($products2)->merge($products3);
			foreach ($products as $product){
				$offer = $doc->createElement('offer');
				$offer->setAttribute('id', $product->id);
				//$offer->setAttribute('type', 'vendor.model');
				$offer->setAttribute('available', 'true');

				$url = $doc->createElement('url', $product->url);
				$price = $doc->createElement('price', $product->price);

				$currency = $doc->createElement('currencyId', 'RUR');
				$category = $doc->createElement('categoryId', $product->catalog_id);
				//$vendor = $doc->createElement('vendor', 'Russia');
				$name = $doc->createElement('name', htmlspecialchars($product->name));
				$product->generateDescription();
				$descr = $product->description;
				$descr = str_replace('{city}', ' в Екатеринбурге', $descr);
				$description = $doc->createElement('description', htmlspecialchars(strip_tags($descr)));

				$offer->appendChild($url);
				$offer->appendChild($price);
				$offer->appendChild($currency);
				$offer->appendChild($category);
				if ($image = $product->image) {
					$image = $doc->createElement('picture', $image->src);
					$offer->appendChild($image);
				}
				$offer->appendChild($name);
				if($description) $offer->appendChild($description);
				$params = [
					'size'	=> 'Толщина/размер (мм)',
					'steel'	=> 'Марка стали',
					'length'	=> 'Длина (м)',
					'gost'	=> 'ГОСТ',
				];
				foreach ($params as $field => $name){
					if($product->{$field}){
						$param = $doc->createElement('param', $product->{$field});
						$param->setAttribute('name', $name);
						$offer->appendChild($param);
					}
				}

				$offers->appendChild($offer);
			}
			$shop->appendChild($offers);

			$path = public_path( 'small_yandex.xml');
			$fh = fopen($path, 'w+');
			fwrite($fh, $doc->saveXML());
			fclose($fh);

			return true;
		} catch (\Exception $e){
			echo $e->getMessage() . "\n" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n";
			return false;
		}
	}
	/**
	 * @param \Swift_Message $message
	 */
	public static function signMessage(&$message) {
//		$privateKey = file_get_contents(base_path('dkim.key'));
//		$signer = new \Swift_Signers_DKIMSigner($privateKey, 'metallresurs.ru', 'mail');
//		$signer->ignoreHeader('Return-Path');
//		$message->attachSigner($signer);
	}

	/**
	 * @param     $src
	 * @param     $path
	 * @param int $attempts
	 *
	 * @return string
	 */
	public static function uploadImage($src, $path, $attempts = 1) {
		echo "download $src \n";
		$upload_path = public_path('uploads' . DIRECTORY_SEPARATOR .
			$path . DIRECTORY_SEPARATOR);
		$ext = pathinfo($src, PATHINFO_EXTENSION);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $src);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208');
		//curl_setopt($ch, CURLOPT_PROXY, "$proxy");

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		$data = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if ($info['http_code'] == 200 && $data) {
			$name = md5(uniqid(rand(), true)) . '.' . $ext;
			$file = $upload_path . $name;
			@file_put_contents($file, $data);

			return $name;
		} elseif ($attempts++ <= 5) return self::uploadImage($src, $path, $attempts + 1);
		else {
			return '';
		}
	}

	public static function getSlideMenu() {
		$city_alias = session('city_alias', '');
		$cache_key = 'catalog.left_menu_mobile_' . $city_alias;
//		$left_menu = Cache::remember($cache_key, env('CACHE_TIME'), function () {
		$left_menu = Cache::remember($cache_key, -1, function () {
			$categories = Catalog::query()
				->whereParentId(0)
				->orderBy('order')
				->with('public_children')
				->withCount('public_children')
				->get();

			$pages = Page::query()
				->whereParentId(1)
				->orderBy('order')
				->with('public_children')
				->withCount('public_children')
				->get();

			return view('blocks.slidemenu', [
				'categories' => $categories,
				'pages'      => $pages
			])->render();
		});

		return $left_menu;
	}

	public static function getCatalogLeftMenu() {
		$city_alias = session('city_alias', '');
		$cache_key = 'catalog.left_menu_' . $city_alias;
		$left_menu = Cache::remember($cache_key, env('CACHE_TIME'), function () {
			$categories = Catalog::query()
				->whereParentId(0)
				->orderBy('order')
				->with('public_children')
				->withCount('public_children')
				->get();

			return view('catalog.left_col', [
				'categories' => $categories,
			])->render();
		});

		return $left_menu;
	}

	public static function getNav() {
		$city_alias = session('city_alias', '');
		$cache_key = 'mobile_nav_' . $city_alias;
		$items = Cache::remember($cache_key, env('CACHE_TIME'), function () {
			$pages = Page::whereOnMain(1)->public()->orderBy('order')->get();

			return view('blocks.nav', ['items' => $pages])->render();
		});

		return $items;
	}

	public static function updateSearchIndex() {
		SearchIndex::update_index();
	}

	static public function autocloseLinks($content) {
		if (empty($content)) {
			return "";
		}
		$html = $content;
		$skip = $_SERVER["HTTP_HOST"];

		return preg_replace_callback(
			"#(<a[^>]+?)>#is", function ($mach) use ($skip) {
			return (
				!($skip
					&& strpos($mach[1], $skip) !== false
					&& strpos($mach[1], 'rel=') === false)
				&& (substr_count($mach[1], 'href="/') == 0 && substr_count($mach[1], 'href=\'/') == 0)
			) ? $mach[1] . ' rel="nofollow">' : $mach[0];
		},
			$html
		);
	}

	public static function replaceLinkToRegion($text, $city = null) {
		$current_city = $city ? $city : self::getCurrentCity();
		$domain = Request::getHttpHost();
		$domain = str_replace(['-', '.'], ['\-', '\.'], $domain);
		$pattern = '/href=[\'"]((http|https)+:\/\/' . $domain . ')?(\/[^\'"]+)[\'"]/isu';
		$cities = City::pluck('alias')->all();
		if (!$current_city) {
			$text = str_replace('{city}', '', $text);
			$text = preg_replace_callback($pattern, function ($match) use ($current_city, $cities) {
				$parts = explode('/', $match[3]);
				if (count($parts) > 1 && in_array($parts[1], $cities)) { //удаляем региональность из ссылки
					unset($parts[1]);
					$match[3] = implode('/', $parts);
				}

				return 'href="' . $match[1] . $match[3] . '"';
			}, $text);

			return $text;
		} else {


			$text = preg_replace_callback($pattern, function ($match) use ($current_city, $cities) {
				$parts = explode('/', $match[3]);
				if (count($parts) > 1 && in_array($parts[1], $cities)) { //меняем региональность в ссылке
					$parts[1] = $current_city->alias;
					$match[3] = implode('/', $parts);
				}

				if (substr($match[3], 0, 7) != '/upload' && substr($match[3], 1, strlen($current_city->alias)) != $current_city->alias) {
					$match[3] = '/' . $current_city->alias . $match[3];
				}

				return 'href="' . $match[1] . $match[3] . '"';
			}, $text);
			$text = str_replace('{city}', ' в ' . $current_city->in_city, $text);

			return $text;
		}
	}

	/**
	 * @param City     $city
	 * @param string[] $links
	 *
	 * @return array
	 */
	public static function getRegionLinkFromFederal($city, $links) {
		if (!is_array($links)) $links = [$links];
		$main_url = url('/') . '/';
		foreach ($links as $key => $cur_url) {
			if ($cur_url == url('/')) {
				$cur_url = '';
			} else {
				$cur_url = str_replace($main_url, '', $cur_url); //отсекаем домен
			}

			if ($cur_url != '') {
				/* не проверяем - региональная ссылка или федеральная */
				$cur_url = $city->alias . '/' . $cur_url;
			} else { //Если на главной
				$cur_url = $city->alias;
			}

			$links[$key] = url($cur_url);
		}

		return $links;
	}

	public static function getUpdateDate() {
//		09:39 18.12.2018
		$date = Cache::remember('update_date', 180, function () {
			return Carbon::now()->subMinutes(rand(1, 180))->format('H:i d.m.Y');
		});

		return $date;
	}

	public static function getCurrentCity() {
		$current_city = new City();

        if (App::bound('CurrentCity')) {
			$current_city = App::make('CurrentCity');
		}

		$city_alias = session('city_alias');
		if (!$current_city->id && $city_alias) {
			$current_city = Cache::remember('city_' . $city_alias, env('CACHE_TIME', -1), function() use ($city_alias){
				return City::whereAlias($city_alias)->first();
			});
		}

		return $current_city && $current_city->id ? $current_city : null;
	}

	public static function clearPhone($text) {
		return preg_replace('/[^\+0-9]/', '', $text);
	}

	/**
	 * @param Image $image
	 *
	 * @return mixed
	 */
	public static function addWaterMark($image) {
		//Watermark
		$water = Image::make(resource_path('water_mark1.png'));
		$water->resize($image->width(), $image->height(), function ($constraint) {
			/** @var \Intervention\Image\Constraint $constraint */
			$constraint->aspectRatio();
			$constraint->upsize();
		});

		$image->insert($water, 'center-center');
		return $image;
	}

	public static function resize_image($file, array $attributes = null, $size = null, $path_only = false, $crop = false) {
		try{
			$base = url('/');
			$file = str_replace($base, '', $file);
			if ($size) {
				$file_info = pathinfo($file);
				$new_path = $file_info['dirname'] . '/' . $size . '/';
				$new_path_full = public_path($file_info['dirname'] . '/' . $size . '/');
				if (!is_dir($new_path_full)) {
					mkdir($new_path_full, 0755, true);
				}
				$resized_file = $new_path . $file_info['basename'];
				if (!is_file(public_path($resized_file))) {
					$image = Image::make(public_path($file));
					$size = explode('x', $size);
					$width = array_get($size, 0);
					$height = array_get($size, 1);
					if ($crop) {
						$image->fit($width, $height);
					} else {
						$image->resize($width, $height, function ($constraint) {
							$constraint->aspectRatio();
							$constraint->upsize();
						});
					}
					$image->save(public_path( $resized_file));
				}
				$file = $resized_file;
			}

			if (strpos($file, '://') === false) {
				// Add the base URL
				$file = url($file);
			}
			if ($path_only === true) {
				return $file;
			}
			// Add the image link
			$attributes['src'] = $file;

			return Html::image($file, null, $attributes);
		} catch (\Exception $e){
			return '';
		}
	}
}
