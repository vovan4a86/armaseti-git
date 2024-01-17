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

class RosmaProducts extends Command
{
    use ParseFunctions;

    protected $signature = 'rosma';
    protected $description = 'Parsing site https://rosma.spb.ru/';
    public $client;
    public $log;

    public $baseUrl = 'https://rosma.spb.ru';

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
        $this->test_catalog();
//        $this->test_product();
        exit();

        foreach ($this->catalogList() as $catName => $catUrl) {
            $this->parseCatalog($catName, $catUrl, 11);
        }

        $this->info('The command was successful!');
    }

    public function catalogList(): array
    {
        return [
            'Манометры' => 'https://rosma.spb.ru/manometers/',
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

            //основные категории раздела
            $has_categories = $catalog_crawler->filter('.product-preview.link')->count();
            if ($has_categories) {
                $catalog_crawler->filter('.product-preview.link')->each(
                    function (Crawler $cat_item) use ($catalog) {
                        $section_name = $cat_item->filter('span')->text();
                        $section_url = $this->baseUrl . $cat_item->attr('href');
                        $this->parseCatalogSection($section_name, $section_url, $catalog->id);
                    }
                );
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseCatalog: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function parseCatalogSection($categoryName, $categoryUrl, $parent = 0)
    {
        $this->info('Парсим подраздел: ' . $categoryName);
        $this->info('Url подраздела: ' . $categoryUrl);
        $sub_catalog = $this->getCatalogByName($categoryName, $parent);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $sub_catalog_crawler = new Crawler($html);

//            //описание раздела
            $has_description = $sub_catalog_crawler->filter('.s-text_product')->count();
            if ($has_description) {
                $sub_catalog->text = $sub_catalog_crawler->filter('.s-text_product')->html();
                $sub_catalog->save();
            }
//
//            //загрузка первого изображения раздела
            $has_image = $sub_catalog_crawler->filter('.s-img')->count();
            if ($has_image && !$sub_catalog->image) {
                $upload_path = Catalog::UPLOAD_URL;
                $file_name = $sub_catalog->alias . '_' . $sub_catalog->id;

                $url = $sub_catalog_crawler->filter('.s-img')->first()->attr('src');
                $ext = $this->getExtensionFromSrc($url);
                $file_name .= $ext;
                $res = $this->downloadJpgFile($url, $upload_path, $file_name);
                if ($res) {
                    $sub_catalog->image = $file_name;
                    $sub_catalog->save();
                }
            }

            //проходимся по таблицам для получения товаров
            $exclude_headers = ['Дополнительные опции']; //исключаемые блоки
            $has_product_sections = $sub_catalog_crawler->filter('.product-section')->count();
            if ($has_product_sections) {
                $sub_catalog_crawler->filter('.product-section')
//                    ->reduce(
//                        function (Crawler $node, $i) {
//                            return ($i < 1);
//                        }
//                    )
                    ->each(
                        function (Crawler $section_block) use ($exclude_headers, $sub_catalog) {
                            //проверяем на пустоту, есть скрытые пустые блоки
                            if ($section_block->filter('.caption h2')->count()) {
                                $section_name = $section_block->filter('.caption h2')->text();
                                if (!in_array($section_name, $exclude_headers)) {
                                    //парсим таблицу
                                    $headers = [];
                                    $section_block->filter('.product-table thead th')->each(
                                        function (Crawler $head_cell) use (&$headers) {
                                            $headers[] = $head_cell->text();
                                        }
                                    );
                                    $count = count($headers) - 2;
                                    $headers_no_name_price = array_splice(
                                        $headers,
                                        1,
                                        $count
                                    ); //убираем 1 столбец - тип и последний Цена = остальное - характеристики-название

                                    $fields = []; //тут все товары с характеристиками
                                    $res = [];

                                    $section_block->filter('.product-table tbody tr')
//                                        ->reduce(
//                                            function (Crawler $node, $i) {
//                                            return ($i > 9 && $i < 16);
//                                            }
//                                        )
                                        ->each(
                                            function (Crawler $tr, $i) use (&$fields, &$self_prop, &$res) {
                                                //строка с названием модели
                                                if ($tr->filter('th')->count()) {
                                                    $name = $tr->filter('th')->text();
                                                    $fields[$i][] = $name;

                                                    $tr->filter('td')->each(
                                                        function (Crawler $td, $n) use (&$fields, $i, $name) {
                                                            //если нет ОБЪЕДИНЕННЫХ СТРОК
                                                            if (!$rowspan = $td->attr('rowspan')) {
                                                                $fields[$i][$n + 1] = $td->text(
                                                                ); //текущее значение запишем
                                                            } else {
                                                                $fields[$i][$n + 1] = $td->text(
                                                                ); //запишем текущее значение

                                                                for ($j = 1; $j < $rowspan; $j++) { //+текущее значение для объеденных след. строк
                                                                    $fields[$i + $j][0] = $name;
                                                                    $fields[$i + $j][$n + 1] = $td->text();
                                                                }
                                                            }
                                                        }
                                                    );
                                                } else {
                                                    //если это строка без заголовка th
                                                    $tr->filter('td')->each(
                                                        function (Crawler $td, $k) use (
                                                            &$res,
                                                            &$self_prop,
                                                            $i,
                                                            &$fields
                                                        ) {
                                                            if ($rowspan = $td->attr('rowspan')) {
                                                                $fields[$i][3] = $td->text(); //запишем текущее значение

                                                                for ($j = 1; $j < $rowspan; $j++) { //+текущее значение для объеденных след. строк
                                                                    $fields[$i + $j][3] = $td->text();
                                                                }
                                                            } else {
                                                                $res[$i][] = $td->text();
                                                            }
                                                        }
                                                    );
                                                }
                                            }
                                        );

                                    //вбиваем пропущенные значения
                                    foreach ($fields as $n => $field) {
                                        if ($n > 0) {
                                            for ($l = 0; $l <= count($fields[0]) - 1; $l++) {
                                                if (!isset($field[$l])) {
                                                    $val = array_shift($res[$n]);
                                                    $fields[$n][$l] = $val;
                                                }
                                            }
                                        }
                                    }

                                    //добавляем все товары в БД + хар-ки
                                    $article_count = 1;
                                    foreach ($fields as $n => $field) {
                                        $last_elem_count = count($field) - 1;
                                        //добавляем циферки в конце имени, чтобы не дублировать имена и артикулы
                                        if ($n !== 0 && $field[0] == $fields[$n - 1][0]) {
                                            $article_count++;
                                        } else {
                                            $article_count = 1;
                                        }

                                        $name = $field[0] . '-' . $article_count;

                                        $product = Product::where('name', $name)->where(
                                            'price',
                                            $field[$last_elem_count]
                                        )->first();

                                        if (!$product) {
                                            $data = [];


                                            $data['catalog_id'] = $sub_catalog->id;
                                            $data['name'] = $name;
                                            $data['title'] = $data['name'];
                                            $data['price'] = $field[$last_elem_count];
                                            $data['article'] = Text::translit($field[0]) . '-' . $article_count;
                                            $data['alias'] = $data['article'];
                                            $data['in_stock'] = 1;
                                            $data['published'] = 1;

                                            $product = Product::create($data);
                                            $this->info('Новый товар: ' . $product->name);

                                            foreach ($headers_no_name_price as $n => $name) {
                                                $char = ProductChar::where('product_id', $product->id)
                                                    ->where('name', $name)->first();

                                                if (!$char) {
                                                    $order = ProductChar::where('product_id', $product->id)->max(
                                                            'order'
                                                        ) + 1;
                                                    ProductChar::create(
                                                        [
                                                            'catalog_id' => $sub_catalog->id,
                                                            'product_id' => $product->id,
                                                            'name' => $name,
                                                            'translit' => Text::translit($name),
                                                            'value' => $field[$n + 1],
                                                            'order' => $order
                                                        ]
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    );
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
        $html = file_get_contents(public_path('test/catalog_rosma.html'));

//        $productPage = $this->client->get('https://protection-chain.ru/catalog/tsepi-protivoskolzheniya/?PAGEN_1=2');
//        $html = $productPage->getBody()->getContents();

        $catalog_crawler = new Crawler($html);

        //категории раздела
//        $catalog_crawler->filter('.product-preview.link')->each(function (Crawler $cat_item) {
//           $name = $cat_item->filter('span')->text();
//           $url = $this->baseUrl . $cat_item->attr('href');
//           dump($name . ' / ' .$url);
//        });

        //категория
        $section_html = file_get_contents(public_path('test/section_rosma.html'));
        $section_crawler = new Crawler($section_html);

//        $has_description = $section_crawler->filter('.s-text_product')->count();
//        if ($has_description) {
//            $description = $section_crawler->filter('.s-text_product')->html();
//        }

        //изображение
//        $has_image = $section_crawler->filter('.s-img')->count();
//        $upload_path = 'test/images/';
//        $file_name = 'section_image';
//        if ($has_image) {
//            $url = $section_crawler->filter('.s-img')->first()->attr('src');
//            $ext = $this->getExtensionFromSrc($url);
//            $file_name .= $ext;
//            $this->downloadJpgFile($url, $upload_path, $file_name);
//        }

        //характеристики ?? все в куче

        //товары из таблицы
        $exclude_headers = ['Дополнительные опции']; //исключаемые блоки
        $has_product_sections = $section_crawler->filter('.product-section')->count();
        if ($has_product_sections) {
            $section_crawler->filter('.product-section')
                ->reduce(
                    function (Crawler $node, $i) {
                        return ($i == 1);
                    }
                )
                ->each(
                    function (Crawler $section_block) use ($exclude_headers) {
                        //проверяем на пустоту, есть скрытые пустые блоки
                        if ($section_block->filter('.caption h2')->count()) {
                            $section_name = $section_block->filter('.caption h2')->text();
                            if (!in_array($section_name, $exclude_headers)) {
                                //парсим таблицу
                                $headers = [];
                                $section_block->filter('.product-table thead th')->each(
                                    function (Crawler $head_cell) use (&$headers) {
                                        $headers[] = $head_cell->text();
                                    }
                                );
                                $count = count($headers) - 2;
                                $headers_no_name_price = array_splice(
                                    $headers,
                                    1,
                                    $count
                                ); //убираем 1 столбец - тип и последний Цена = остальное - характеристики-название

                                $products = [];
                                $res = [];

                                $section_block->filter('.product-table tbody tr')
                                    ->reduce(
                                        function (Crawler $node, $i) {
//                                            return ($i > 9 && $i < 16);
                                        }
                                    )
                                    ->each(
                                        function (Crawler $tr, $i) use (&$products, &$self_prop, &$res) {
                                            //строка с названием модели
                                            if ($tr->filter('th')->count()) {
                                                $name = $tr->filter('th')->text();
                                                $products[$i][] = $name;

                                                $tr->filter('td')->each(
                                                    function (Crawler $td, $n) use (&$products, $i, $name) {
                                                        //если нет ОБЪЕДИНЕННЫХ СТРОК
                                                        if (!$rowspan = $td->attr('rowspan')) {
                                                            $products[$i][$n + 1] = $td->text(
                                                            ); //текущее значение запишем
                                                        } else {
                                                            $products[$i][$n + 1] = $td->text(
                                                            ); //запишем текущее значение

                                                            for ($j = 1; $j < $rowspan; $j++) { //+текущее значение для объеденных след. строк
                                                                $products[$i + $j][0] = $name;
                                                                $products[$i + $j][$n + 1] = $td->text();
                                                            }
                                                        }
                                                    }
                                                );
                                            } else {
                                                //если это строка без заголовка th
                                                $tr->filter('td')->each(
                                                    function (Crawler $td, $k) use (&$res, &$self_prop, $i, &$products) {
                                                        if ($rowspan = $td->attr('rowspan')) {
                                                            $products[$i][3] = $td->text(); //запишем текущее значение

                                                            for ($j = 1; $j < $rowspan; $j++) { //+текущее значение для объеденных след. строк
                                                                $products[$i + $j][3] = $td->text();
                                                            }
                                                        } else {
                                                            $res[$i][] = $td->text();
                                                        }
                                                    }
                                                );
                                            }
                                        }
                                    );

                                dump($res);
//                                exit();
                                //вбиваем пропущенные значения
                                foreach ($products as $n => $field) {
                                    if ($n > 0) {
                                        for ($l = 0; $l <= count($products[0]) - 1; $l++) {
                                            if (!isset($field[$l]) && isset($res[$n])) {
                                                $val = array_shift($res[$n]);
                                                $products[$n][$l] = $val;
                                            }
                                        }
                                    }
                                }
                                dd($products);


                                exit();
                                //добавляем все товары в БД + хар-ки
                                $article_count = 1;
                                foreach ($products as $n => $field) {
                                    $last_elem_count = count($field) - 1;
                                    $product = Product::where('name', $field[0])->where(
                                        'price',
                                        $field[$last_elem_count]
                                    )->first();

                                    if (!$product) {
                                        $data = [];
                                        //добавляем циферки в конце имени, чтобы не дублировать имена и артикулы
                                        if ($n !== 0 && $field[0] == $products[$n - 1][0]) {
                                            $article_count++;
                                        } else {
                                            $article_count = 1;
                                        }

                                        $data['catalog_id'] = 12;
                                        $data['name'] = $field[0] . '-' . $article_count;
                                        $data['title'] = $data['name'];
                                        $data['price'] = $field[$last_elem_count];
                                        $data['article'] = Text::translit($field[0]) . '-' . $article_count;
                                        $data['alias'] = $data['article'];
                                        $data['in_stock'] = 1;
                                        $data['published'] = 1;

                                        $product = Product::create($data);

                                        foreach ($headers_no_name_price as $n => $name) {
                                            $char = ProductChar::where('product_id', $product->id)
                                                ->where('name', $name)->first();

                                            if (!$char) {
                                                $order = ProductChar::where('product_id', $product->id)->max(
                                                        'order'
                                                    ) + 1;
                                                ProductChar::create(
                                                    [
                                                        'catalog_id' => 12,
                                                        'product_id' => $product->id,
                                                        'name' => $name,
                                                        'translit' => Text::translit($name),
                                                        'value' => $field[$n + 1],
                                                        'order' => $order
                                                    ]
                                                );
                                            }
                                        }
                                    }
                                    exit();
                                }
                            }
                        }
                    }
                );
        }
    }

    public function test_product()
    {
        $html = file_get_contents(public_path('test/product_ast.html'));
        $product_crawler = new Crawler($html);

//        $has_main_photo = $product_crawler->filter('.product_photo')->count();
//        if ($has_main_photo) {
//            $image_src = $product_crawler->filter('.product_photo a')->attr('href');
//            dump($image_src);
//        }
//
//        $has_dop_photo = $product_crawler->filter('.product_dop_photo .fancybox')->count();
//        if ($has_dop_photo) {
//            $product_crawler->filter('.product_dop_photo .fancybox')
//                //пропускаем первое фото в дополнительных, тк оно было главным
//                ->reduce(
//                    function ($node, $i) {
//                        return ($i > 0);
//                    }
//                )
//                ->each(
//                    function (Crawler $image) {
//                        $image_src = $image->attr('href');
//                        dump($image_src);
//                    }
//                );
//        }

        //короткое описание, есть и большое, но лучше просто скачать в документы Полное описание
//            $has_short_description = $product_crawler->filter('.short_description')->count();
//            if ($has_short_description) {
//                $short = $product_crawler->filter('.short_description')->html();
//            }

        //характеристики, есть не у всех. в описании есть полные - но они общей таблицей.
//        $has_chars = $product_crawler->filter('.dop_atr .prod_dop_option')->count();
//        if ($has_chars) {
//            $product_crawler->filter('.dop_atr .prod_dop_option')->each(function (Crawler $row) {
//                $string = trim($row->text());
//                //получаем строку 'Имя: значение'
//                $arr = explode(':', $string);
//                if (count($arr) == 2) {
//                    $name = trim($arr[0]);
//                    $value = trim($arr[1]);
//                    dump($name . ':' . $value);
//                }
//            });
//        }

        //скачать описание 1(0) блок - описание
//        $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(0)->count();
//        if ($has_description_block) {
//            $doc_src = $product_crawler->filter('.product_dop_modes_content')->eq(0)->filter('a')->first()->attr('href');
//            $pdf_src = null;
//            if ($this->checkIsFileDoc($doc_src)) {
//                $pdf_src = $this->baseUrl . $doc_src;
//            }
//
//            if ($pdf_src) {
//                dump($pdf_src); //качаем описание pdf
//            }
//        }

        //техпаспорт
//        $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(1)->count();
//        if ($has_description_block) {
//            $doc_src = $product_crawler->filter('.product_dop_modes_content')->eq(1)->filter('a')->first()->attr('href');
//            $pdf_src = null;
//            if ($this->checkIsFileDoc($doc_src)) {
//                $pdf_src = $this->baseUrl . $doc_src;
//            }
//
//            if ($pdf_src) {
//                dump($pdf_src); //качаем описание pdf
//            }
//        }

        //сертификаты
        $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(2)->count();
        if ($has_description_block) {
            $product_crawler->filter('.product_dop_modes_content')->eq(2)->filter('a')->each(
                function (Crawler $link) {
                    if ($name = $link->text()) {
                        $doc_src = $link->attr('href');
                        $pdf_src = null;
                        $name = trim(str_replace('Скачать', '', $name));
                        if ($this->checkIsFileDoc($doc_src)) {
                            $pdf_src = $this->baseUrl . $doc_src;
                        }

                        if ($pdf_src) {
                            dump($name . ' : ' . $pdf_src); //качаем описание pdf
                        }
                    }
                }
            );
        }
    }
}
