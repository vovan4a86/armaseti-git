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

    public $baseUrl = 'https://asteama.ru';

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
//        $this->test_product();

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

                $product->update(['article' => 'ast000' . $product->id]);

                $has_main_photo = $product_crawler->filter('.product_photo')->count();
                if ($has_main_photo) {
                    $image_url = $product_crawler->filter('.product_photo a')->attr('href');
                    $ext = $this->getExtensionFromSrc($image_url);
                    $file_name = $product->article . $ext;
                    $upload_path = ProductImage::UPLOAD_URL . $catalog->alias . '/';

                    $res = $this->downloadJpgFile($image_url, $upload_path, $file_name);
                    if ($res) {
                        ProductImage::create(
                            [
                                'product_id' => $product->id,
                                'image' => $file_name,
                                'order' => 0
                            ]
                        );
                    }
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
                                $file_name = $product->article . '_' . ($i + 1) . $ext;
                                $upload_path = ProductImage::UPLOAD_URL . $catalog->alias . '/';

                                $res = $this->downloadJpgFile($image_url, $upload_path, $file_name);
                                if ($res) {
                                    ProductImage::create(
                                        [
                                            'product_id' => $product->id,
                                            'image' => $file_name,
                                            'order' => ($i + 1)
                                        ]
                                    );
                                }
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
                                    $char = ProductChar::where('product_id', $product->id)
                                        ->where('name', $name)->first();

                                    if (!$char) {
                                        $char = ProductChar::create(
                                            [
                                                'catalog_id' => $catalog->id,
                                                'product_id' => $product->id,
                                                'name' => $name,
                                                'translit' => Text::translit($name),
                                                'value' => $value,
                                                'order' => ProductChar::where('product_id', $product->id)->max('order')
                                            ]
                                        );
                                    }
                                }

                                //добавляем название характеристики в фильтр главного раздела
                                $root_cat = $catalog->findRootCategory();

                                $parent_char = ParentCatalogFilter::where('catalog_id', $root_cat->id)
                                    ->where('name', $name)
                                    ->first();

                                if (!$parent_char) {
                                    ParentCatalogFilter::create(
                                        [
                                            'catalog_id' => $root_cat->id,
                                            'name' => $char->name,
                                            'published' => 1,
                                            'order' => ParentCatalogFilter::where('catalog_id', $root_cat->id)
                                                    ->max('order') + 1
                                        ]
                                    );
                                }
                            }
                        }
                    );
                }

//            скачать описание 1(0) блок - описание
                $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(0)->count();
                if ($has_description_block) {
                    $doc_src = $product_crawler->filter('.product_dop_modes_content')->eq(0)->filter('a')->first(
                    )->attr(
                        'href'
                    );
                    $pdf_src = null;
                    if ($this->checkIsFileDoc($doc_src)) {
                        $pdf_src = $this->baseUrl . $doc_src;
                    }

                    if ($pdf_src) {
                        $ext = $this->getExtensionFromSrc($pdf_src);
                        $file_name = Text::translit('Полное описание') . '_' . $product->article . $ext;
                        $file_path = ProductDoc::UPLOAD_URL . $catalog->alias . '/';

                        try {
                            $res = $this->downloadPdfFile($pdf_src, $file_path, $file_name);
                            if ($res) {
                                ProductDoc::create(
                                    [
                                        'product_id' => $product->id,
                                        'name' => 'Полное описание',
                                        'file' => $file_name,
                                        'order' => 0
                                    ]
                                );
                            }
                        } catch (\Exception $e) {
                            $this->error('404, файл не найден!');
                        }
                    }

                    $text = $product_crawler->filter('.product_dop_modes_content')->eq(0)->html();

                    $product->text = $this->cutDescriptionFromTextHead($text);
                    $product->save();
                }

//            техпаспорт
//            $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(1)->count();
//            if ($has_description_block) {
//                $doc_src = $product_crawler->filter('.product_dop_modes_content')->eq(1)->filter('a')->first()->attr(
//                    'href'
//                );
//                $pdf_src = null;
//                if ($this->checkIsFileDoc($doc_src)) {
//                    $pdf_src = $this->baseUrl . $doc_src;
//                }
//
//                if ($pdf_src) {
//                    dump($pdf_src); //качаем описание pdf
//                }
//            }

                //сертификаты
//            $has_description_block = $product_crawler->filter('.product_dop_modes_content')->eq(2)->count();
//            if ($has_description_block) {
//                $product_crawler->filter('.product_dop_modes_content')->eq(2)->filter('a')->each(
//                    function (Crawler $link) {
//                        if ($name = $link->text()) {
//                            $doc_src = $link->attr('href');
//                            $pdf_src = null;
//                            $name = trim(str_replace('Скачать', '', $name));
//                            if ($this->checkIsFileDoc($doc_src)) {
//                                $pdf_src = $this->baseUrl . $doc_src;
//                            }
//
//                            if ($pdf_src) {
//                                dump($name . ' : ' . $pdf_src); //качаем описание pdf
//                            }
//                        }
//                    }
//                );
//            }


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
//            $doc_src = $product_crawler->filter('.product_dop_modes_content')->eq(0)->filter('a')->first()->attr('href');
//            $pdf_src = null;
//            if ($this->checkIsFileDoc($doc_src)) {
//                $pdf_src = $this->baseUrl . $doc_src;
//            }
//
//            if ($pdf_src) {
//                dump($pdf_src); //качаем описание pdf
//            }
            $text = $product_crawler->filter('.product_dop_modes_content')->eq(0)->html();

            dump($this->cutDescriptionFromTextHead($text));
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
