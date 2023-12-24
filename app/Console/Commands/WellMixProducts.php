<?php

namespace App\Console\Commands;

use App\Traits\ParseFunctions;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\CatalogDoc;
use Fanky\Admin\Models\ParentCatalogFilter;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductChar;
use Fanky\Admin\Models\ProductDoc;
use Fanky\Admin\Models\ProductImage;
use Fanky\Admin\Text;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class WellMixProducts extends Command
{
    use ParseFunctions;

    protected $signature = 'well';
    protected $description = 'Parsing site https://wellmix-pump.ru/catalog/';
    public $client;
    public $log;

    public $baseUrl = 'https://wellmix-pump.ru';

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client(
            [
                'headers' => ['User-Agent' => Arr::random($this->userAgents)],
                'allow_redirects' => true,
                'http_errors' => false,
                'verify' => false // если сайт http
            ]
        );
    }

    public function handle()
    {
//        $this->test_catalog();
        $this->test_product();

//        foreach ($this->catalogList() as $catName => $catUrl) {
//            $this->parseCatalog($catName, $catUrl, 7);
//        }

        $this->info('The command was successful!');
    }

    public function catalogList(): array
    {
        return [
            'Циркуляционные насосы с мокрым ротором' => 'https://wellmix-pump.ru/catalog/tsirkulyatsionnye_nasosy_s_mokrym_rotorom/',
//            'Циркуляционные насосы с сухим ротором' => 'https://wellmix-pump.ru/catalog/tsirkulyatsionnye_nasosy_s_sukhim_rotorom/',
//            'Вертикальные многоступенчатые центробежные насосы in-line' => 'https://wellmix-pump.ru/catalog/vertikalnye_mnogostupenchatye_tsentrobezhnye_nasosy_in_line/',
//            'Горизонтальные многоступенчатые центробежные насосы' => 'https://wellmix-pump.ru/catalog/gorizontalnye_mnogostupenchatye_tsentrobezhnye_nasosy/',
//            'Моноблочные насосы' => 'https://wellmix-pump.ru/catalog/monoblochnye_nasosy/',
//            'Канализационные насосы' => 'https://wellmix-pump.ru/catalog/kanalizatsionnye_nasosy/',
//            'Бытовые насосы для водоснабжения' => 'https://wellmix-pump.ru/catalog/nasosy_dlya_chastnykh_domov/',
        ];
    }

    public function parseCatalog($categoryName, $categoryUrl, $parent = 0)
    {
        $this->info('Парсим раздел: ' . $categoryName);
        $this->info('Url раздела: ' . $categoryUrl);
        $catalog = $this->getCatalogByName($categoryName, $parent);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $catalog_crawler = new Crawler($html);

            try {
                //изображение перед описанием раздела (https://gremir.ru/zaglushki-stalnye/category_1687/)
                $uploadCatalogTextImagesPath = '/uploads/catalogs-content/';
                $pre_text = null;
                if ($catalog_crawler->filter('.page-main__detail')->count() > 0) {
                    $text_image = $catalog_crawler->filter('.page-main__detail')->html();

                    $i = $catalog_crawler->filter('.detail__image-wrapper img')->count();
                    $imgSrc = [];
                    $imgArr = [];
                    if ($i > 0) {
                        $catalog_crawler->filter('.detail__image-wrapper img')
                            ->each(
                                function (Crawler $image, $i) use ($uploadCatalogTextImagesPath, &$imgSrc, &$imgArr) {
                                    $url = $image->attr('src');
                                    $arr = explode('/', $url);
                                    $file_name = array_pop($arr);
                                    $file_name = str_replace('%20', '_', $file_name);

                                    if ($this->checkIsImageJpg($file_name)) {
                                        if (!is_file(public_path($uploadCatalogTextImagesPath . $file_name))) {
                                            $this->downloadJpgFile($url, $uploadCatalogTextImagesPath, $file_name);
                                        }

                                        $imgSrc[] = $url;
                                        $imgArr[] = $uploadCatalogTextImagesPath . $file_name;
                                    }
                                }
                            );
                    }
                    $pre_text = $this->getUpdatedTextWithNewImages($text_image, $imgSrc, $imgArr);
                }
            } catch (\Exception $e) {
                $this->error('Ошибка получения изображения в описании раздела');
            }

            //описание
            if ($catalog_crawler->filter('.category-desc')->count() > 0) {
                $text = $catalog_crawler->filter('.category-desc')->html();

                if ($pre_text) {
                    $catalog->text = $pre_text . $text;
                } else {
                    $catalog->text = $text;
                }
                $catalog->save();
            }

            //документация
            if ($catalog_crawler->filter('.product-documentation')->count() > 0) {
                $catalog_crawler->filter('.docs__item')->each(
                    function (Crawler $item, $i) use ($catalog) {
                        $name = trim($item->filter('.docs__link')->text());
                        $url = $this->baseUrl . $item->filter('.docs__link')->attr('href');
                        $arr = explode('/', $url);
                        $url_full_file_name = array_pop($arr);

                        if (str_ends_with($url_full_file_name, 'pdf')) {
                            if (!is_file(
                                public_path(CatalogDoc::UPLOAD_URL . $catalog->alias . '/' . $url_full_file_name)
                            )) {
                                $this->downloadPdfFile(
                                    $url,
                                    CatalogDoc::UPLOAD_URL . $catalog->alias . '/',
                                    $url_full_file_name
                                );
                            }

                            $catalog_doc = CatalogDoc::where('catalog_id', $catalog->id)
                                ->where('file', $url_full_file_name)->first();

                            if (!$catalog_doc) {
                                CatalogDoc::create(
                                    [
                                        'catalog_id' => $catalog->id,
                                        'name' => $name,
                                        'file' => $url_full_file_name,
                                        'order' => $i
                                    ]
                                );
                            }
                        }
                    }
                );
            }

            if ($catalog_crawler->filter('.sub-links')->count() > 0) {
                $catalog_crawler->filter('.sub-links li')
                    ->each(
                        function (Crawler $item, $i) use ($catalog, $catalog_crawler) {
                            $url = $this->baseUrl . $item->filter('a')->attr('href');
                            $name = trim($item->filter('.cat-name')->text());

                            //зациклились подразделы
                            if (in_array($name, [
                                    'Трубы нержавеющие',
                                    'Трубы полипропиленовые',
                                    'Трубы SML',
                                    'Трубы опорные SML',
                                    'Трубы полиэтиленовые',
                                    'Трубы НПВХ',
                                    'Трубы клеевые НПВХ',
                                    'Трубы канализационные',
                                    'Трубы ЧК',
                                    'Трубы из сшитого полиэтилена',
                                    'Трубы асбестоцементные'
                                ]
                            )) {
                                $this->alert('Skip! ' . $name);
//                                $catalog_crawler->filter('.prod-list.prod-list-top tr')
//                                    ->each(
//                                        function (Crawler $list_item, $i) use ($catalog) {
//                                            $name = trim($list_item->filter('.list2-title')->text());
//                                            $url = $this->baseUrl . $list_item->filter('.list2-title')->attr('href');
//
//                                            // если товар уже парсили, обновим цену/наличие и дальше
//                                            $product = Product::where('parse_url', $url)->first();
//                                            if (!$product) {
//                                                $this->parseProduct($catalog, $name, $url);
//                                            } else {
//                                                //изображения
//                                                $this->updateProduct($product, $url);
//                                            }
//                                        }
//                                    );
                            } else {
                                $this->parseCatalog($name, $url, $catalog->id);
                            }
                        }
                    );
            } else {
                if ($parent != 0) {
                    //парсим список товаров в разделе
                    $catalog_crawler->filter('.prod-list.prod-list-top tr')
                        ->each(
                            function (Crawler $list_item, $i) use ($catalog) {
                                $data = [];

                                $name = trim($list_item->filter('.list2-title')->text());
                                $url = $this->baseUrl . $list_item->filter('.list2-title')->attr('href');

//                                if ($list_item->filter('.product-count--outstock')->count() > 0) {
//                                    $data['product_count'] = 'Под заказ'; // наличие
//                                } elseif ($list_item->filter('.product-count--instock')->count() > 0) {
//                                    $product_count_text = $list_item->filter('.product-count--instock .nowrap')->text();
//                                    $data['product_count'] = preg_replace(
//                                        "/[^0-9]/",
//                                        '',
//                                        $product_count_text
//                                    ); // наличие
//                                } else {
//                                    $data['product_count'] = null;
//                                }

//                                //цена из списка товаров
//                                $price_text = trim($list_item->filter('.td-price span')->text());
//                                if ($price_text == 'по запросу') {
//                                    $data['price'] = '0';
//                                } else {
//                                    $data['price'] = preg_replace("/[^0-9]/", '', $price_text); // цена
//                                }

                                // если товар уже парсили, обновим цену/наличие и дальше
                                $product = Product::where('parse_url', $url)->first();
                                if (!$product) {
                                    $this->parseProduct($catalog, $name, $url);
                                } else {
//                                    $this->updateProduct($product, $url, $catalog);
                                }
                            }
                        );

//                    проход по следующим страницам
                    if ($catalog_crawler->filter('.menu-h')->count() > 0) {
                        $last_li_class = $catalog_crawler->filter('.menu-h li a')->last()->attr('class');
                        if ($last_li_class == 'inline-link') {
                            $url = $this->baseUrl . $catalog_crawler->filter('.menu-h li a')->last()->attr('href');
                            $this->parseCatalogNextPage($url, $catalog);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseCatalog: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function parseCustomCatalog($categoryName, $categoryUrl, $parent = 0)
    {
        $this->info('Парсим раздел: ' . $categoryName);
        $this->info('Url раздела: ' . $categoryUrl);
        $catalog = $this->getCatalogByName($categoryName, $parent);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $catalog_crawler = new Crawler($html);

            try {
                //изображение перед описанием раздела (https://gremir.ru/zaglushki-stalnye/category_1687/)
                $uploadCatalogTextImagesPath = '/uploads/catalogs-content/';
                $pre_text = null;
                if ($catalog_crawler->filter('.page-main__detail')->count() > 0) {
                    $text_image = $catalog_crawler->filter('.page-main__detail')->html();

                    $i = $catalog_crawler->filter('.detail__image-wrapper img')->count();
                    $imgSrc = [];
                    $imgArr = [];
                    if ($i > 0) {
                        $catalog_crawler->filter('.detail__image-wrapper img')
                            ->each(
                                function (Crawler $image, $i) use ($uploadCatalogTextImagesPath, &$imgSrc, &$imgArr) {
                                    $url = $image->attr('src');
                                    $arr = explode('/', $url);
                                    $file_name = array_pop($arr);
                                    $file_name = str_replace('%20', '_', $file_name);

                                    if ($this->checkIsImageJpg($file_name)) {
                                        if (!is_file(public_path($uploadCatalogTextImagesPath . $file_name))) {
                                            $this->downloadJpgFile($url, $uploadCatalogTextImagesPath, $file_name);
                                        }

                                        $imgSrc[] = $url;
                                        $imgArr[] = $uploadCatalogTextImagesPath . $file_name;
                                    }
                                }
                            );
                    }
                    $pre_text = $this->getUpdatedTextWithNewImages($text_image, $imgSrc, $imgArr);
                }
            } catch (\Exception $e) {
                $this->error('Ошибка получения изображения в описании раздела');
            }

            //описание
            if ($catalog_crawler->filter('.category-desc')->count() > 0) {
                $text = $catalog_crawler->filter('.category-desc')->html();

                if ($pre_text) {
                    $catalog->text = $pre_text . $text;
                } else {
                    $catalog->text = $text;
                }
                $catalog->save();
            }

            //документация
            if ($catalog_crawler->filter('.product-documentation')->count() > 0) {
                $catalog_crawler->filter('.docs__item')->each(
                    function (Crawler $item, $i) use ($catalog) {
                        $name = trim($item->filter('.docs__link')->text());
                        $url = $this->baseUrl . $item->filter('.docs__link')->attr('href');
                        $arr = explode('/', $url);
                        $url_full_file_name = array_pop($arr);

                        if (str_ends_with($url_full_file_name, 'pdf')) {
                            if (!is_file(
                                public_path(CatalogDoc::UPLOAD_URL . $catalog->alias . '/' . $url_full_file_name)
                            )) {
                                $this->downloadPdfFile(
                                    $url,
                                    CatalogDoc::UPLOAD_URL . $catalog->alias . '/',
                                    $url_full_file_name
                                );
                            }

                            $catalog_doc = CatalogDoc::where('catalog_id', $catalog->id)
                                ->where('file', $url_full_file_name)->first();

                            if (!$catalog_doc) {
                                CatalogDoc::create(
                                    [
                                        'catalog_id' => $catalog->id,
                                        'name' => $name,
                                        'file' => $url_full_file_name,
                                        'order' => $i
                                    ]
                                );
                            }
                        }
                    }
                );
            }


            //парсим список товаров в разделе
            $catalog_crawler->filter('.prod-list.prod-list-top tr')
                ->each(
                    function (Crawler $list_item, $i) use ($catalog) {
                        $data = [];

                        $name = trim($list_item->filter('.list2-title')->text());
                        $url = $this->baseUrl . $list_item->filter('.list2-title')->attr('href');

                        $product = Product::where('parse_url', $url)->first();
                        if (!$product) {
                            $this->parseProduct($catalog, $name, $url);
                        }
                    }
                );

//                    проход по следующим страницам
            if ($catalog_crawler->filter('.menu-h')->count() > 0) {
                $last_li_class = $catalog_crawler->filter('.menu-h li a')->last()->attr('class');
                if ($last_li_class == 'inline-link') {
                    $url = $this->baseUrl . $catalog_crawler->filter('.menu-h li a')->last()->attr('href');
                    $this->parseCatalogNextPage($url, $catalog);
                }
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseCatalog: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function parseCatalogNextPage($nextUrl, Catalog $catalog)
    {
        try {
            $this->warn('Следующая станица: ' . $nextUrl);
            $res = $this->client->get($nextUrl);
            $html = $res->getBody()->getContents();
            $catalog_crawler = new Crawler($html);

            //парсим список товаров в разделе
            $catalog_crawler->filter('.prod-list.prod-list-top tr')
                ->each(
                    function (Crawler $list_item, $i) use ($catalog) {
                        $name = trim($list_item->filter('.list2-title')->text());
                        $url = $this->baseUrl . $list_item->filter('.list2-title')->attr('href');

                        $product = Product::where('parse_url', $url)->first();
                        if (!$product) {
                            $this->parseProduct($catalog, $name, $url);
                        }
                    }
                );

            //проход по следующим страницам
            if ($catalog_crawler->filter('.menu-h')->count() > 0) {
                $last_li_class = $catalog_crawler->filter('.menu-h li a')->last()->attr('class');
                if ($last_li_class == 'inline-link') {
                    $url = $this->baseUrl . $catalog_crawler->filter('.menu-h li a')->last()->attr('href');
                    $this->parseCatalogNextPage($url, $catalog);
                }
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseCatalogNextPage: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function parseProduct(Catalog $catalog, $name, $url)
    {
        $this->info('Новый товар: ' . $name . ' (' . $url . ')');

        try {
            $res = $this->client->get($url);
            $html = $res->getBody()->getContents();
            $product_crawler = new Crawler($html); //products page from url

            $data = [];

            $data['catalog_id'] = $catalog->id;
            $data['name'] = $name;
            $data['alias'] = Text::translit($name);
            $data['h1'] = $name;
            $data['title'] = $name;
            $data['published'] = 1;
            $data['parse_url'] = $url;
            $data['order'] = Product::where('catalog_id', $catalog->id)->max('order') + 1;

            // цена
            if ($product_crawler->filter('.product-price .price')->count() > 0) {
                $data['price'] = $product_crawler->filter('.product-price .price')->attr('data-price');
            }

            // артикул
            if ($product_crawler->filter('.product-cart-top span.bold')->count()) {
                $data['article'] = trim($product_crawler->filter('.product-cart-top span.bold')->text());
            }

            // наличие
            if ($product_crawler->filter('.product-count--outstock')->count() > 0) {
//                $data['product_count'] = 'Под заказ';
                $data['in_stock'] = 0;
            } elseif ($product_crawler->filter('.product-count--instock')->count() > 0) {
//                $product_count_text = $product_crawler->filter('.product-count--instock .nowrap')->text();
//                $data['product_count'] = preg_replace("/[^0-9]/", '', $product_count_text);
                $data['in_stock'] = 1;
            }

            //Паспорт в описании??
            $uploadTextImagesPath = '/uploads/products-content/';
            if ($product_crawler->filter('.description .passport__wrapper')->count() > 0) {
                $text = $product_crawler->filter('.description .passport__wrapper')->html();

                $i = $product_crawler->filter('.description .passport__wrapper img')->count();
                $imgSrc = [];
                $imgArr = [];
                if ($i > 0) {
                    $product_crawler->filter('.description .passport__wrapper img')
                        ->each(
                            function (Crawler $image, $i) use ($uploadTextImagesPath, &$imgSrc, &$imgArr) {
                                $url = $image->attr('src');
                                $arr = explode('/', $url);
                                $file_name = array_pop($arr);
                                $file_name = str_replace('%20', '_', $file_name);

                                if ($this->checkIsImageJpg($file_name)) {
                                    if (!is_file(public_path($uploadTextImagesPath . $file_name))) {
                                        $this->downloadJpgFile($url, $uploadTextImagesPath, $file_name);
                                    }

                                    $imgSrc[] = $url;
                                    $imgArr[] = $uploadTextImagesPath . $file_name;
                                }
                            }
                        );
                }
                $data['text'] = $this->getUpdatedTextWithNewImages($text, $imgSrc, $imgArr);
            }

            $product = Product::create($data);

            //характеристики
            if ($product_crawler->filter('#product-features')->count() > 0) {
                $product_crawler->filter('#product-features tr')->each(
                    function (Crawler $tr, $i) use ($product, $catalog) {
                        $name_text = trim($tr->filter('td.name')->text());
                        $name = preg_replace("/:/", '', $name_text);

                        $char = ProductChar::where('product_id', $product->id)->where('name', $name)->first();
                        if (!$char) {
                            $value = trim($tr->filter('td.value')->text());
                            $char = ProductChar::create(
                                [
                                    'catalog_id' => $product->catalog_id,
                                    'product_id' => $product->id,
                                    'name' => $name,
                                    'translit' => Text::translit($name),
                                    'value' => $value,
                                    'order' => ProductChar::where('product_id', $product->id)->max('order') + 1
                                ]
                            );
                        }
                        //добавляем название характеристики в фильтр главного раздела
                        $root_cat = $catalog->findRootCategory();

                        $parent_char = ParentCatalogFilter::where('catalog_id', $root_cat->id)
                            ->where('name', $char->name)
                            ->first();

                        if (!$parent_char) {
                            ParentCatalogFilter::create(
                                [
                                    'catalog_id' => $root_cat->id,
                                    'name' => $char->name,
                                    'published' => 1,
                                    'order' => ParentCatalogFilter::where('catalog_id', $root_cat->id)->max('order') + 1
                                ]
                            );
                        }

                        //старый вариант для таблицы catalog_filters
//                        $cat_filter = CatalogFilter::where('catalog_id', $product->catalog_id)->where(
//                            'name',
//                            $name
//                        )->first();
//                        if (!$cat_filter) {
//                            CatalogFilter::create(
//                                [
//                                    'catalog_id' => $product->catalog_id,
//                                    'name' => $name,
//                                    'order' => CatalogFilter::where('catalog_id', $product->catalog_id)->max(
//                                            'order'
//                                        ) + 1,
//                                    'published' => 1
//                                ]
//                            );
//                        }
                    }
                );
            }

            //изображения
            if ($product_crawler->filter('.imgs')->count() > 0) {
                // 1 изображение
                if ($product_crawler->filter('.imgs .image')->count() == 1) {
                    $url_raw = null;
                    if ($product_crawler->filter('.imgs .image a')->count() > 0) {
                        $url_raw = $product_crawler->filter('.imgs .image a')->attr('href');
                    } elseif ($product_crawler->filter('.imgs .image img')->count() > 0) {
                        $url_raw = $product_crawler->filter('.imgs .image img')->first()->attr('src');
                    }

                    if ($url_raw) {
                        $url = $this->baseUrl . $url_raw;
                        $url_arr = explode('.', $url_raw);
                        $ext = array_pop($url_arr);
                        $file_name = $data['article'] . '.' . $ext;
                        $this->uploadProductImage($url, $file_name, $product->id, $catalog->alias);
                    }
                }

                // больше 1 изображения
                if ($product_crawler->filter('.imgs .more-images .image')->count() > 1) {
                    $product_crawler->filter('.imgs .more-images .image')->each(
                        function (Crawler $image, $i) use ($product, $data, $catalog) {
                            $url_raw = $image->filter('a')->attr('href');
                            $url = $this->baseUrl . $url_raw;
                            $url_arr = explode('.', $url_raw);
                            $ext = array_pop($url_arr);
                            $file_name = $data['article'] . '_' . ($i + 1) . '.' . $ext;
                            $this->uploadProductImage($url, $file_name, $product->id, $catalog->alias);
                        }
                    );
                }
            }

            //документы
            if ($product_crawler->filter('.product-documentation')->count() > 0) {
                $product_crawler->filter('.docs__item')->each(
                    function (Crawler $item, $i) use ($product, $catalog) {
                        $name = trim($item->filter('.docs__link')->text());
                        $url = $this->baseUrl . $item->filter('.docs__link')->attr('href');
                        $arr = explode('/', $url);
                        $url_full_file_name = array_pop($arr);

                        try {
                            if (str_ends_with($url, 'pdf')) {
                                if (!is_file(
                                    public_path(ProductDoc::UPLOAD_URL . $catalog->alias . '/' . $url_full_file_name)
                                )) {
                                    $this->downloadPdfFile(
                                        $url,
                                        ProductDoc::UPLOAD_URL . $catalog->alias . '/',
                                        $url_full_file_name
                                    );
                                }

                                $product_doc = ProductDoc::where('product_id', $product->id)
                                    ->where('file', $url_full_file_name)->first();

                                if (!$product_doc) {
                                    ProductDoc::create(
                                        [
                                            'product_id' => $product->id,
                                            'name' => $name,
                                            'file' => $url_full_file_name,
                                            'order' => $i
                                        ]
                                    );
                                }
                            }
                        } catch (\Exception $e) {
                            $this->error('Ошибка скачивания файла: ' . $e->getMessage());
                            $this->error($product->name . ' => id=' . $product->id);
                        }
                    }
                );
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseProduct: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function updateProduct(Product $product, $url, $catalog)
    {
        $this->info('Обновляем товар: ' . $product->name);

        try {
            $res = $this->client->get($url);
            $html = $res->getBody()->getContents();
            $product_crawler = new Crawler($html); //products page from url

//            $product->update(['catalog_id' => $catalog->id]);

        } catch (\Exception $e) {
            $this->error('Ошибка parseProduct: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function uploadProductImage($url, $file_name, $product_id, $catalog_alias)
    {
        if (!is_file(public_path(ProductImage::UPLOAD_URL . $catalog_alias . '/' . $file_name))) {
            $this->downloadJpgFile($url, ProductImage::UPLOAD_URL . $catalog_alias . '/', $file_name);
        }
        $img = ProductImage::where('product_id', $product_id)->where('image', $file_name)->first();
        if (!$img) {
            ProductImage::create(
                [
                    'product_id' => $product_id,
                    'image' => $file_name,
                    'order' => ProductImage::where('product_id', $product_id)->max('order') + 1
                ]
            );
        }
    }

    public function test_catalog()
    {
        $html = file_get_contents(public_path('test/catalog_well.html'));

//        $productPage = $this->client->get('https://protection-chain.ru/catalog/tsepi-protivoskolzheniya/?PAGEN_1=2');
//        $html = $productPage->getBody()->getContents();

        $catalog_crawler = new Crawler($html);

        //parse sections
        $hasSections = $catalog_crawler->filter('.list.items .item')->count();
//        if ($hasSections) {
//            $catalog_crawler->filter('.list.items .item')->each(function (Crawler $section) {
//               $name = trim($section->filter('.name a')->text());
//               $url = $this->baseUrl . $section->filter('.name a')->attr('href');
//            });
//        }

        $hasDesc = $catalog_crawler->filter('.group_description_block')->count();
        if ($hasDesc) {
            $desc = $catalog_crawler->filter('.group_description_block')->html();
        }


        //parse products
        $hasProducts = $catalog_crawler->filter('.item_block')->count();
        if ($hasProducts) {
            $catalog_crawler->filter('.item_block')->each(function (Crawler $item) {
                $name = trim($item->filter('.item-title a span')->text());
                $url = $this->baseUrl . $item->filter('.item-title a')->attr('href');
            });
        }
    }

    public function test_product()
    {
        $html = file_get_contents(public_path('test/product_well.html'));
        $product_crawler = new Crawler($html);

        //name ??
        $name = trim($product_crawler->filter('.page-top-main h1')->text());

        //description
        $has_descr = $product_crawler->filter('.descr-outer-wrapper .detail_text')->count();
        if ($has_descr) {
            $descr = $product_crawler->filter('.descr-outer-wrapper .detail_text')->html();

            $descr_crawler = new Crawler($descr);

            $has_images = $descr_crawler->filter('img')->count();
            if ($has_images) {
                $imgs_arr = [];
                $upload_path = '/test/catalog/text/';
                $file_name = 'name';
                $descr_crawler->filter('img')
                    ->each(function (Crawler $image, $i) use ($file_name, $upload_path) {
                   $url = $this->baseUrl . $image->attr('data-src');
                   $ext = $this->getExtensionFromSrc($url);
                   $file_name .= '_' . $i . $ext;
                });
            }

        }


        //docs
//        $has_docs = $product_crawler->filter('.files_block')->count();
//        if ($has_docs) {
//            $product_crawler->filter('.files_block')->each(function (Crawler $block) {
//                $block->filter('.row')->children()->each(function (Crawler $file) {
//                    $name = trim($file->filter('.description a')->text());
//                    $url = $this->baseUrl . $file->filter('.description a')->attr('href');
//                });
//            });
//        }

        exit();

        $js = $product_crawler->filter('.sku_props')->children()->last()->text();
        $start = strpos($js, 'OFFERS') + 8;
        $end = strpos($js, 'OFFER_SELECTED') - 2;
        $length = $end - $start;
        $res = substr($js, $start, $length);
        $res_quotes = str_replace("'", "\"", $res);
//        file_put_contents(public_path('test/file/file.json'), $res_quotes);
//        $file = file_get_contents(public_path('test/file/file.json'));

        $obj = json_decode($res_quotes, true);

        $ids = [];
        foreach ($obj as $n => $item) {
            $chars = $item['DISPLAY_PROPERTIES'];
            $chars_all = [];
            foreach ($chars as $elem) {
                $chars_all[] = [$elem['NAME'], $elem['VALUE']];
            }

            $images = $item['SLIDER'];
            $images_all = [];
            foreach ($images as $image) {
                $images_all[] = $this->baseUrl . $image['SRC'];
            }

            $ids[] = [
                'id' => $item['ID'],
                'name' => $item['NAME'],
                'article' => $item['DISPLAY_PROPERTIES_CODE']['ARTICLE']['VALUE'],
                'price' => $item['PRICE']['VALUE'],
                'chars' => $chars_all,
                'images' => $images_all,
                'in_stock' => $item['COUNT_IN_ALL_WAREHOUSES']
            ];
            var_dump($ids);

            exit();
        }
        var_dump($ids);
    }
}
