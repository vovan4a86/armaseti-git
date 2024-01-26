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

class AsteamaProducts extends Command
{
    use ParseFunctions;

    protected $signature = 'ast';
    protected $description = 'Parsing site https://asteama.ru/';
    public $client;
    public $log;

    public $baseUrl = 'https://npoasta.ru/';

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
        $url = 'https://prompribor-kaluga.ru/local/ajax/ajax_item.php';
        $response = $this->client->request('POST', $url);
        dd($response);

//        $this->test_catalog();
//        $this->test_product();
        exit();

        foreach ($this->catalogList() as $catName => $catUrl) {
            $this->parseCatalog($catName, $catUrl, 15);
        }

        $this->info('The command was successful!');
    }

    public function catalogList(): array
    {
        return [
            'Предохранительные клапаны' => 'https://npoasta.ru/catalog/safety_valve/',
        ];
    }

    public function parseCatalog($categoryName, $categoryUrl, $parent = 0)
    {
        $this->info('Парсим раздел: ' . $categoryName . ' (' . $categoryUrl . ')');
        $catalog = $this->getCatalogByName($categoryName, $parent);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $catalog_crawler = new Crawler($html);

            //описание
            $has_cat_description = $catalog_crawler->filter('.catalog-description__bottom')->count();
            if ($has_cat_description && !$catalog->text) {
                $catalog->text = $catalog_crawler->filter('.catalog-description__bottom')->html();
                $catalog->save();
            }

            //смотрим есть ли подразделы
            $has_sub_cats = $catalog_crawler->filter('.category-list__item')->count();
            if ($has_sub_cats) {
                $catalog_crawler->filter('.category-list__item')->each(
                    function (Crawler $sub_cat) use ($catalog) {
                        $name = trim($sub_cat->filter('.category-list__item-title')->text());
                        $url = $this->baseUrl . $sub_cat->filter('a')->attr('href');

                        if (!$catalog->image) {
                            $img_url = $sub_cat->filter('.category-list__item-image ')->attr('src');
                            $file_name = $catalog->alias . '_' . $catalog->id;
                            $ext = $this->getExtensionFromSrc($img_url);
                            $file_name .= '.' . $ext;
                            $upload_path = Catalog::UPLOAD_URL;

                            $res = $this->downloadJpgFile($img_url, $upload_path, $file_name);
                            if ($res) {
                                $catalog->image = $file_name;
                                $catalog->save();
                            }
                        }

                        $this->parseCatalog($name, $url, $catalog->id);
                    }
                );
            } else {
                $has_products = $catalog_crawler->filter('.catalog-item')->count();
                if ($has_products) {
                    $catalog_crawler->filter('.catalog-item')->each(
                        function (Crawler $item) use ($catalog) {
                            $name = trim($item->filter('.show_product')->innerText());
                            $url = $item->filter('.show_product')->attr('href');
                            $this->parseProduct($catalog, $name, $url);
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

    public function parseProduct(Catalog $catalog, $name, $url)
    {
        $this->info('Новый товар: ' . $name . ' (' . $url . ')');

        try {
            $res = $this->client->get($url);
            $html = $res->getBody()->getContents();
            $product_crawler = new Crawler($html); //products page from url

            $data = [];

            $product = Product::where('parse_url', $url)->first();
            if (!$product) {
                $data['catalog_id'] = $catalog->id;
                $data['name'] = $name;
                $data['alias'] = Text::translit($name);
                $data['h1'] = $name;
                $data['title'] = $name;
                $data['published'] = 1;
                $data['parse_url'] = $url;

                $product = Product::create($data);
                $article = 'AST-' . $product->id;
                $product->update(['article' => $article]);

                $has_main_photo = $product_crawler->filter('.product_photo')->count();
                if ($has_main_photo) {
                    $image_url = $product_crawler->filter('.product_photo a')->attr('href');
                    $ext = $this->getExtensionFromSrc($image_url);
                    $file_name = $product->article . $ext;

                    $this->uploadProductImage($image_url, $file_name, $product);
                }

                $has_dop_photo = $product_crawler->filter('.product_dop_photo .fancybox')->count();
                if ($has_dop_photo) {
                    $product_crawler->filter('.product_dop_photo .fancybox')
                        //пропускаем первое фото в дополнительных, тк оно было главным
                        ->reduce(
                            function ($node, $i) {
                                return ($i > 0);
                            }
                        )
                        ->each(
                            function (Crawler $image, $i) use ($data, $catalog, $product) {
                                $image_url = $image->attr('href');
                                $ext = $this->getExtensionFromSrc($image_url);
                                $file_name = $product->article . '_' . $i . $ext;

                                $this->uploadProductImage($image_url, $file_name, $product);
                            }
                        );
                }

//            короткое описание, есть и большое, но лучше просто скачать в документы Полное описание
//            $has_short_description = $product_crawler->filter('.short_description')->count();
//            if ($has_short_description) {
//                $product->text = $product_crawler->filter('.short_description')->html();
//                $product->save();
//            }

//            характеристики, есть не у всех . в описании есть полные - но они общей таблицей .
                $has_chars = $product_crawler->filter('.dop_atr .prod_dop_option')->count();
                if ($has_chars) {
                    $product_crawler->filter('.dop_atr .prod_dop_option')->each(
                        function (Crawler $row) use ($catalog, $product) {
                            $string = trim($row->text());
                            //получаем строку 'Имя: значение'
                            $arr = explode(':', $string);
                            if (count($arr) == 2) {
                                $name = trim($arr[0]);
                                $value = trim($arr[1]);

                                if ($name && $value) {
                                    $this->createProductCharWithParentCatalog($name, $value, $product, $catalog);
                                }
                            }
                        }
                    );
                }

//                скачать описание 1(0) блок - описание
                $has_description_block = $product_crawler->filter('.product_dop_modes_content')
                    ->eq(0)->count();
                if ($has_description_block) {
                    $doc_src = $product_crawler->filter('.product_dop_modes_content')
                        ->eq(0)->filter('a')->first()->attr('href');
                    $pdf_src = null;
                    if ($this->checkIsFileDoc($doc_src)) {
                        $pdf_src = $this->baseUrl . $doc_src;
                    }

                    if ($pdf_src) {
                        $ext = $this->getExtensionFromSrc($pdf_src);
                        $name = 'Полное описание';
                        $file_name = Text::translit('Полное описание') . '_' . $product->article . $ext;

                        try {
                            $this->uploadProductDoc($pdf_src, $file_name, $name, $product);
                        } catch (\Exception $e) {
                            $this->error('404, файл не найден!');
                        }
                    }

                    $text = $product_crawler->filter('.product_dop_modes_content')->eq(0)->html();

                    $text = $this->cutDescriptionFromTextHead($text);

                    $text_crawler = new Crawler($text);
                    $i = $text_crawler->filter('img')->count();
                    if ($i > 0) {
                        $imgSrc = [];
                        $imgArr = [];
                        $uploadCatalogTextImagesPath = '/uploads/catalogs-content-test/' . $catalog->alias . '/';
                        $text_crawler->filter('img')
                            ->each(
                                function (Crawler $image) use ($uploadCatalogTextImagesPath, &$imgArr, &$imgSrc) {
                                    $raw_url = $image->attr('src');
                                    $url = $this->baseUrl . $raw_url;
                                    $arr = explode('/', $raw_url);
                                    $file_name = array_pop($arr);
                                    $file_name = str_replace('%20', '_', $file_name);

                                    if ($this->checkIsImageJpg($file_name)) {
                                        if (!is_file(public_path($uploadCatalogTextImagesPath . $file_name))) {
                                            $this->downloadJpgFile($url, $uploadCatalogTextImagesPath, $file_name);
                                        }

                                        $imgSrc[] = $raw_url;
                                        $imgArr[] = $uploadCatalogTextImagesPath . $file_name;
                                    }
                                }
                            );
                        $clean_text = $this->getUpdatedTextWithNewImages($text, $imgSrc, $imgArr);
                        $product->text = $clean_text;
                    } else {
                        $product->text = $text;
                    }
                    $product->save();
                }

//            техпаспорт
                $has_description_block = $product_crawler->filter('.product_dop_modes_content')
                    ->eq(1)->count();
                if ($has_description_block) {
                    $doc_src = $product_crawler->filter('.product_dop_modes_content')
                        ->eq(1)->filter('a')->first()->attr('href');
                    $pdf_src = null;
                    if ($this->checkIsFileDoc($doc_src)) {
                        $pdf_src = $this->baseUrl . $doc_src;
                    }
                    $ext = $this->getExtensionFromSrc($pdf_src);
                    $name = 'Техпаспорт';
                    $file_name = 'tech_passport_' . $product->article . $ext;

                    $this->uploadProductDoc($pdf_src, $file_name, $name, $product);
                }

                //сертификаты
                $has_description_block = $product_crawler->filter('.product_dop_modes_content')
                    ->eq(2)->count();
                if ($has_description_block) {
                    $product_crawler->filter('.product_dop_modes_content')->eq(2)->filter('a')->each(
                        function (Crawler $link) use ($product, $catalog) {
                            if ($name = $link->text()) {
                                $doc_src = $link->attr('href');
                                $pdf_src = null;
                                $name = trim(str_replace('Скачать', '', $name));
                                if ($this->checkIsFileDoc($doc_src)) {
                                    $pdf_src = $this->baseUrl . $doc_src;
                                }

                                if ($pdf_src) {
                                    $ext = $this->getExtensionFromSrc($doc_src);
                                    $file_name = $name . '_sertificat_' . $product->article . $ext;
                                    $upload_path = ProductDoc::UPLOAD_URL . $catalog->alias . '/';

                                    $this->uploadProductDoc($pdf_src, $file_name, $name, $product);
                                }
                            }
                        }
                    );
                }
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseProduct: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
        exit();
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

    public function test_catalog()
    {
//        $html = file_get_contents(public_path('test/catalog_ast.html'));

//        $productPage = $this->client->get('https://protection-chain.ru/catalog/tsepi-protivoskolzheniya/?PAGEN_1=2');
//        $html = $productPage->getBody()->getContents();

//        $catalog_crawler = new Crawler($html);
//
//        $has_cat_description = $catalog_crawler->filter('.catalog-description__bottom')->count();
//        if ($has_cat_description) {
//            $description = $catalog_crawler->filter('.catalog-description__bottom')->html();
//        }
//
//        $has_sub_cats = $catalog_crawler->filter('.category-list__item')->count();
//        if ($has_sub_cats) {
//            $catalog_crawler->filter('.category-list__item')->each(function (Crawler $sub_cat) {
//                $name = trim($sub_cat->filter('.category-list__item-title')->text());
//                $url = $this->baseUrl . $sub_cat->filter('a')->attr('href');
//                $img_src = $sub_cat->filter('.category-list__item-image ')->attr('src');

        //parseCatalog();
//            });
//        } else {
        //parseCatalogList();
//          }

        //parse catalog list

//        $has_cat_description = $catalog_crawler_list->filter('.catalog-description__bottom')->count();
//        if ($has_cat_description) {
//            $description = $catalog_crawler_list->filter('.catalog-description__bottom')->html();
//        }

        $html_list = file_get_contents(public_path('test/catalog_list.html'));
        $catalog_crawler_list = new Crawler($html_list);

        $has_products = $catalog_crawler_list->filter('.catalog-item')->count();
        if ($has_products) {
            $catalog_crawler_list->filter('.catalog-item')->each(
                function (Crawler $item) {
                    $name = trim($item->filter('.show_product')->innerText());
                    $url = $item->filter('.show_product')->attr('href');
                    //parseProduct();
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
        $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(0)->count();
        if ($has_description_block) {
            $doc_src = $product_crawler->filter('.product_dop_modes_content')->eq(0)->filter('a')->first()->attr(
                'href'
            );
            $pdf_src = null;
            if ($this->checkIsFileDoc($doc_src)) {
                $pdf_src = $this->baseUrl . $doc_src;
            }

            if ($pdf_src) {
                dump($pdf_src); //качаем описание pdf
            }
            $text = $product_crawler->filter('.product_dop_modes_content')->eq(0)->html();
            $text = $this->cutDescriptionFromTextHead($text);

            $text_crawler = new Crawler($text);
            $i = $text_crawler->filter('img')->count();
            if ($i > 0) {
                $imgSrc = [];
                $imgArr = [];
                $uploadCatalogTextImagesPath = '/uploads/catalogs-content-test/';
                $text_crawler->filter('img')
                    ->each(
                        function (Crawler $image) use ($uploadCatalogTextImagesPath, &$imgArr, &$imgSrc) {
                            $raw_url = $image->attr('src');
                            $url = $this->baseUrl . $raw_url;
                            $arr = explode('/', $raw_url);
                            $file_name = array_pop($arr);
                            $file_name = str_replace('%20', '_', $file_name);

                            if ($this->checkIsImageJpg($file_name)) {
                                if (!is_file(public_path($uploadCatalogTextImagesPath . $file_name))) {
                                    $this->downloadJpgFile($url, $uploadCatalogTextImagesPath, $file_name);
                                }

                                $imgSrc[] = $raw_url;
                                $imgArr[] = $uploadCatalogTextImagesPath . $file_name;
                            }
                        }
                    );
                $clean_text = $this->getUpdatedTextWithNewImages($text, $imgSrc, $imgArr);
                dd($clean_text);
            }
        }

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
//        $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(2)->count();
//        if ($has_description_block) {
//            $product_crawler->filter('.product_dop_modes_content')->eq(2)->filter('a')->each(
//                function (Crawler $link) {
//                    if ($name = $link->text()) {
//                        $doc_src = $link->attr('href');
//                        $pdf_src = null;
//                        $name = trim(str_replace('Скачать', '', $name));
//                        if ($this->checkIsFileDoc($doc_src)) {
//                            $pdf_src = $this->baseUrl . $doc_src;
//                        }
//
//                        if ($pdf_src) {
//                            dump($name . ' : ' . $pdf_src); //качаем описание pdf
//                        }
//                    }
//                }
//            );
//        }
    }

    //только для npoasta - убираем из текста надпись Цена по запросу и скачать полное описание
    public function cutDescriptionFromTextHead($text)
    {
        for ($i = 1; $i <= 2; $i++) {
            $start = stripos($text, '<p>');
            $end = stripos($text, '</p>');
            if ($start && $end) {
                $text = substr_replace($text, '', $start, ($end + 4) - $start);
            }
        }

        return $text;
    }

}
