<?php

namespace App\Console\Commands;

use App\Traits\ParseFunctions;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\CatalogDoc;
use Fanky\Admin\Models\CatalogFilter;
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
use League\CommonMark\Block\Element\Document;
use Symfony\Component\DomCrawler\Crawler;

class AdlProducts extends Command
{
    use ParseFunctions;

    protected $signature = 'adl';
    protected $description = 'https://adl.ru/truboprovodnaya-armatura/ventili-zapornye/';
    public $client;
    public $log;

    public $baseUrl = 'https://adl.ru';

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
//        $this->test_product_list();
//        $this->test_product();
        exit();
        $products = Product::with('chars')->get();
        $total = count($products);
        foreach ($products as $n => $product) {
            $this->info(($n + 1) . ' / ' . $total);
            $catalog_id = $product->catalog_id;
            $chars = $product->chars;

            if (count($chars)) {
                foreach ($chars as $char) {
                    $catalog_filter = CatalogFilter::whereCatalogId($catalog_id)
                        ->whereName($char->name)
                        ->first();

                    if (!$catalog_filter) {
                        CatalogFilter::create([
                            'catalog_id' => $catalog_id,
                            'name' => $char->name,
                            'published' => 1,
                            'order' => CatalogFilter::whereCatalogId($catalog_id)->max('order') + 1
                        ]);
                    }
                }
            }
        }
        exit();

        $parent = Catalog::where('name', 'Вентили')->first();
        foreach ($this->catalogList() as $catName => $catUrl) {
            $this->parseSections($catName, $catUrl, $parent->id);
        }

        $this->info('The command was successful!');
    }

    public function catalogList(): array
    {
        return [
            'Вентили запорные' => 'https://adl.ru/truboprovodnaya-armatura/ventili-zapornye/'
        ];
    }

    public function parseSections($categoryName, $categoryUrl, $parent = 0)
    {
        $this->info('Парсим раздел: ' . $categoryName . ' (' . $categoryUrl . ')');
        $catalog = $this->getCatalogByName($categoryName, $parent);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $catalog_crawler = new Crawler($html);

            $has_items = $catalog_crawler->filter('.item.box__item')->count();
            if ($has_items) {
                $catalog_crawler->filter('.item.box__item')
                    ->reduce(
                        function (Crawler $node, $i) {
                            return ($i == 0);
                        }
                    )
                    ->each(
                        function (Crawler $section) use ($catalog) {
                            $name = $section->filter('.name a')->text();
                            $url = $this->baseUrl . $section->filter('.name a')->attr('href');
                            $this->parseCatalog($name, $url, $catalog);
                        }
                    );
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseSection: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function parseCatalog($categoryName, $categoryUrl, Catalog $catalog)
    {
        $this->info('Парсим раздел c товарами: ' . $categoryName . ' (' . $categoryUrl . ')');

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $catalog_list_crawler = new Crawler($html);

            //изображение раздела
            if (!$catalog->image) {
                if ($catalog_list_crawler->filter('.catalog-element-photos-full-item')->count()) {
                    $image_src = $this->baseUrl . $catalog_list_crawler->filter(
                            '.catalog-element-photos-full-item a'
                        )->attr('href');
                    $ext = $this->getExtensionFromSrc($image_src);
                    $upload_path = Catalog::UPLOAD_URL;
                    $file_name = $catalog->alias . '_' . $catalog->id;
                    $file_name .= $ext;

                    $res = $this->downloadJpgFile($image_src, $upload_path, $file_name);
                    if ($res) {
                        $catalog->update(['image' => $file_name]);
                    }
                };
            }

            //описание раздела
            if (!$catalog->text) {
                $catalog->text = $catalog_list_crawler->filter('#description')->html();
                $catalog->save();
            }

            //Характеристики общие раздела, затем некоторые поля изменяться под конкретный товар
            $common_chars = [];
            $has_chars = $catalog_list_crawler->filter('.catalog-element-characteristics-table tr')->count();
            if ($has_chars) {
                $catalog_list_crawler->filter('.catalog-element-characteristics-table tr')
                    ->each(
                        function (Crawler $tr) use (&$common_chars) {
                            $name = $tr->children()->eq(0)->text();
                            $val = $tr->children()->eq(1)->text();
                            $common_chars[$name] = $val;
                        }
                    );
            }

            //товары вытягиваем ajax-ом
            $tablep = $catalog_list_crawler->filter('#offers_table_container')->attr('date-tablep');
//            $url = 'https://adl.ru/truboprovodnaya-armatura/ventili-zapornye-granvent-kv16.html';
            $pagequery = 1;
            $ajax_url = $categoryUrl . '?tablep=' . $tablep . '&' . $pagequery;

            $ajax_res = $this->client->get($ajax_url);
            $ajax_html = $ajax_res->getBody()->getContents();
            $ajax_crawler = new Crawler($ajax_html);

            $has_product_table = $ajax_crawler->filter('.table__intro tr')->count();
            if ($has_product_table) {
                $ajax_crawler->filter('.table__intro tr')->each(
                    function (Crawler $tr) use (&$common_chars, $catalog, $categoryUrl) {
                        $article = $tr->filter('.art_col_f span')->text(); //артикул
                        $size = $tr->filter('td')->eq(1)->text(); //габариты
                        $dn = $tr->filter('td')->eq(2)->text(); //DN
                        $kv = $tr->filter('td')->eq(3)->text(); //KV
                        $in_stock_text = trim($tr->filter('td')->eq(4)->text());
                        $in_stock = $in_stock_text == 'В наличии' ? 1 : 0;
                        $price = $tr->filter('.table_price')->text();
                        $price = str_replace(' ', '', $price);

                        //в общих характеристиках изменяем отдельные характеристики для товара
                        $common_chars['DN'] = $dn;
                        $common_chars['Kv, м3/ч'] = $kv;
                        $common_chars['Габариты'] = $size;

                        //добавляем товар в БД + хар-ки
                        $product = Product::where('article', $article)->first();
                        if (!$product) {
                            $product = Product::create(
                                [
                                    'catalog_id' => $catalog->id,
                                    'name' => $article,
                                    'article' => $article,
                                    'alias' => Text::translit($article),
                                    'h1' => $article,
                                    'title' => $article,
                                    'price' => $price,
                                    'in_stock' => $in_stock,
                                    'published' => 1,
                                    'order' => Product::where('catalog_id', $catalog->id)->max('order') + 1,
                                    'parse_url' => $categoryUrl
                                ]
                            );

                            //характеристики
                            foreach ($common_chars as $name => $value) {
                                $this->createProductCharWithParentCatalog($name, $value, $product, $catalog);
                            }
                        }
                    }
                );
            }

            //Документы
            //проверяем что это вкладка Документы, а не Каталог
            $tab = $catalog_list_crawler->filter('#characteristics')->nextAll()->first()->filter('b')->first()->text();
            if ($tab !== 'Каталог') {
                $tabDocs = $catalog_list_crawler->filter('#characteristics')->nextAll()->first()->html();
                $tab_crawler = new Crawler($tabDocs);

                $has_files = $tab_crawler->filter('.list-model-files .elem-file')->count();
                if ($has_files) {
                    $tab_crawler->filter('.list-model-files .elem-file')
                        ->each(
                            function (Crawler $node, $i) use ($catalog) {
                                $name = $node->filter('a')->text();
                                $url = $this->baseUrl . $node->filter('a')->attr('href');
                                //убираем в конце url артефакт => '?'
                                if ($url[strlen($url) - 1] == '?') {
                                    $url = substr($url, 0, -1);
                                }
                                $ext = $this->getExtensionFromSrc($url);
                                $upload_path = CatalogDoc::UPLOAD_URL . $catalog->alias . '/';
                                $file_name = $catalog->alias . '_' . $i;
                                $file_name .= $ext;

                                $res = $this->downloadFile($url, $upload_path, $file_name);
                                if ($res) {
                                    $doc = CatalogDoc::where('catalog_id', $catalog->id)->where('name', $name)->first();
                                    if (!$doc) {
                                        CatalogDoc::create(
                                            [
                                                'catalog_id' => $catalog->id,
                                                'name' => $name,
                                                'file' => $file_name,
                                                'order' => CatalogDoc::where('catalog_id', $catalog->id)
                                                        ->max('order') + 1
                                            ]
                                        );
                                    }
                                }
                            }
                        );
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
                                $this->downloadFile(
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
                                    $this->downloadFile(
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

    public function test_product_list()
    {
        $html = file_get_contents(public_path('test/catalog_adl.html'));

//        $productPage = $this->client->get('https://protection-chain.ru/catalog/tsepi-protivoskolzheniya/?PAGEN_1=2');
//        $html = $productPage->getBody()->getContents();

        $product_list_crawler = new Crawler($html);

        $has_items = $product_list_crawler->filter('.item.box__item')->count();
        if ($has_items) {
            $product_list_crawler->filter('.item.box__item')
                ->reduce(
                    function (Crawler $node, $i) {
                        return ($i == 0);
                    }
                )
                ->each(
                    function (Crawler $section, $i) {
                        $name = $section->filter('.name a')->text();
                        $url = $this->baseUrl . $section->filter('.name a')->attr('href');

                        $image = $this->baseUrl . $section->filter('.pic a img')->attr('src');

                        $this->info($image);
                    }
                );
        }
    }

    public function test_product()
    {
        $html = file_get_contents(public_path('test/product_adl.html'));
        $product_crawler = new Crawler($html);

        //Описание
//        $description = $product_crawler->filter('#description')->html();

        //Характеристики ?? общие раздела
        $common_chars = [];
        $has_chars = $product_crawler->filter('.catalog-element-characteristics-table tr')->count();
        if ($has_chars) {
            $product_crawler->filter('.catalog-element-characteristics-table tr')
                ->each(
                    function (Crawler $tr) use (&$common_chars) {
                        $name = $tr->children()->eq(0)->text();
                        $val = $tr->children()->eq(1)->text();
                        $common_chars[$name] = $val;
                    }
                );
        }

        //Документы
        //проверяем что это вкладка Документы, а не Каталог
//        $tab = $product_crawler->filter('#characteristics')->nextAll()->first()->filter('b')->first()->text();
//        if ($tab !== 'Каталог') {
//            $tabDocs = $product_crawler->filter('#characteristics')->nextAll()->first()->html();
//            $tab_crawler = new Crawler($tabDocs);
//
//            $has_files = $tab_crawler->filter('.list-model-files .elem-file')->count();
//            if ($has_files) {
//                $tab_crawler->filter('.list-model-files .elem-file')->each(function (Crawler $node, $i) {
//                    $name = $node->filter('a')->text();
//                    $url = $this->baseUrl . $node->filter('a')->attr('href');
//                    //убираем в конце url артефакт => '?'
//                    if ($url[strlen($url) - 1] == '?') {
//                        $url = substr($url, 0, -1);
//                    }
//                    $this->info($name);
//                });
//            }
//        }
//        exit();

        //товары вытягиваем ajax-ом
//        $tablep = $product_crawler->filter('#offers_table_container')->attr('date-tablep');
//        $url = 'https://adl.ru/truboprovodnaya-armatura/ventili-zapornye-granvent-kv16.html';
//        $pagequery = 1;
//        $ajax_url = $url . '?tablep=' . $tablep . '&' . $pagequery;

//        $ajax_html = file_get_contents($ajax_url);
//        file_put_contents(public_path('test/table.html'), $ajax_html);

        $ajax_html = file_get_contents(public_path('test/table.html'));
        $ajax_crawler = new Crawler($ajax_html);

        $catalog = Catalog::where('name', 'Вентили запорные')->first();

        $has_product_table = $ajax_crawler->filter('.table__intro tr')->count();
        if ($has_product_table) {
            $ajax_crawler->filter('.table__intro tr')->each(
                function (Crawler $tr) use ($common_chars, $catalog) {
                    $article = $tr->filter('.art_col_f span')->text(); //артикул
                    $size = $tr->filter('td')->eq(1)->text(); //габариты
                    $dn = $tr->filter('td')->eq(2)->text(); //DN
                    $kv = $tr->filter('td')->eq(3)->text(); //KV
                    $in_stock_text = trim($tr->filter('td')->eq(4)->text());
                    $in_stock = $in_stock_text == 'В наличии' ? 1 : 0;
                    $price = $tr->filter('.table_price')->text();
                    $price = str_replace(' ', '', $price);

                    //в общих характеристиках изменяем отдельные характеристики для товара
                    $common_chars['DN'] = $dn;
                    $common_chars['Kv, м3/ч'] = $kv;
                    $common_chars['Габариты'] = $size;

                    //добавляем товар в БД + хар-ки
                    $product = Product::where('article', $article)->first();
                    if (!$product) {
                        $product = Product::create(
                            [
                                'catalog_id' => $catalog->id,
                                'name' => $article,
                                'article' => $article,
                                'alias' => Text::translit($article),
                                'h1' => $article,
                                'title' => $article,
                                'price' => $price,
                                'in_stock' => $in_stock,
                                'published' => 1,
                                'order' => 0,
                                'parse_url' => 'https://adl.ru/truboprovodnaya-armatura/ventili-zapornye-granvent-kv16.html'
                            ]
                        );

                        //характеристики
                        foreach ($common_chars as $name => $value) {
                            $this->createProductCharWithParentCatalog($name, $value, $product, $catalog);
                        }
                    }
                }
            );
        }
    }
}
