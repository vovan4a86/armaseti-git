<?php

namespace App\Console\Commands;

use App\Traits\ParseFunctions;
use Carbon\Carbon;
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

class RidanProducts extends Command
{
    use ParseFunctions;

    protected $signature = 'ridan';
    protected $description = 'Parsing site https://ridan.ru/catalog/pump_equipment';
    public $client;
    public $log;

    public $baseUrl = 'https://ridan.ru';

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
//        exit();

        // ВНИМАТЕЛЬНО!!!!! PARENT КАТАЛОГА КУДА КАЧАЕМ
        foreach ($this->catalogList() as $catName => $catUrl) {
//            $this->parseCatalog($catName, $catUrl, 834); // РАБОЧИЙ PARENT!
            $this->parseCatalog($catName, $catUrl, 2); //Ридан
        }

        $this->info('The command was successful!');
    }

    public function catalogList(): array
    {
        return [
            'Насосы циркуляционные с мокрым ротором RW Ридан' => 'https://ridan.ru/catalog/pump_equipment/nasosy-cirkulyacionnye-s-mokrym-rotorom-rw',
//            'Насосы одноступенчатые вертикальные ин-лайн RV Ридан' => 'https://ridan.ru/catalog/pump_equipment/nasosy-odnostupencatye-vertikalnye-in-lain-rv',
//            'Насосы вертикальные многоступенчатые RMV Ридан' => 'https://ridan.ru/catalog/pump_equipment/nasosy-vertikalnye-mnogostupencatye-rmv',
//            'Насосные станции WaterJump' => 'https://ridan.ru/catalog/pump_equipment/nasosnye-stancii-waterjump',
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

            if (!$catalog->text) {
                $text = $catalog_crawler->filter('.product-card__card-content .product-card__block')->eq(2)->html();
                $catalog->update(['text' => $text]);
            }

            if (!$catalog->image) {
                $image_url = $catalog_crawler->filter('.carousel-item.active')->attr('href');
                if ($image_url) {
                    $image_url = $this->baseUrl . $image_url;
                    $upload_path = Catalog::UPLOAD_URL;
                    $file_name = $catalog->alias . '_' . $catalog->id;

                    $res = $this->downloadJpgFile($image_url, $upload_path, $file_name);
                    if ($res) {
                        $catalog->update(['image' => $file_name]);
                    }
                }
            }

            $product_links = [];
            $n = 0;
            $url = '';
            $article = '';
            $name = '';
            $catalog_crawler->filter('.series-products__body table tbody tr')
                ->each(function (Crawler $row, $i) use (&$product_links, &$n, &$url, &$article, &$name) {
                    //первый tr - название/артикул/ссылка, второй - цена
                    if ($i % 2 == 0) {
                        $url = $this->baseUrl . $row->filter('.link-primary')->attr('href');
                        $article = $row->filter('.link-primary')->text();
                        $name = $row->filter('.text-break')->text();
                    }
                    if ($i % 2 != 0) {
                        $price = $row->filter('.series-products__price span[data-product-price]')->text();
                        $price = str_replace(' ', '', $price);
                        $product_links[$n] = [$url, $article, $name, $price];
                        $n++;
                    }
                });

            foreach ($product_links as $data) {
                $url = $data[0];
                $product = Product::whereParseUrl($url)->first();
                if (!$product) {
                    $this->parseProduct($url, $catalog, $data);
                } else {
                    $product->update(['price' => $data[3], 'updated_at' => Carbon::now()]);
                }
                exit();
            }

        } catch (\Exception $e) {
            $this->error('Ошибка parseCatalog: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            Log::channel('parser')->error($e->getMessage());
            Log::channel('parser')->error($e->getTraceAsString());
            exit();
        }
    }

    public function parseProduct($url, Catalog $catalog, $product_data)
    {
        $data = [];

        $data['article'] = $product_data[1];
        $data['name'] = $product_data[2];
        $data['price'] = $product_data[3];
        $this->info('Новый товар: ' . $data['name'] . ' (' . $url . ')');

        try {
            $res = $this->client->get($url);
            $html = $res->getBody()->getContents();
            $product_crawler = new Crawler($html); //products page from url

            $data['catalog_id'] = $catalog->id;
            $data['alias'] = Text::translit($data['name']);
            $data['h1'] = $data['name'];
            $data['title'] = $data['name'];
            $data['published'] = 1;
            $data['parse_url'] = $url;
            $data['order'] = Product::where('catalog_id', $catalog->id)->max('order') + 1;
            $data['in_stock'] = $data['price'] ? 1 : 0;

            $product = Product::create($data);

            //изображения
            $has_images = $product_crawler->filter('.carousel-item')->count();
            if ($has_images) {
                $product_crawler->filter('.carousel-item')
                    ->each(function (Crawler $img, $i) use ($product) {
                        $url = $this->baseUrl . $img->attr('href');
                        $ext = $this->getExtensionFromSrc($url);

                        $file_name = $product->article;
                        if ($i > 0) {
                            $file_name = $product->article . '_' . ($i + 1);
                        }
                        $file_name .= $ext;

                        $this->uploadProductImage($url, $file_name, $product);
                    });
            }

            //характеристики
            $product_crawler->filter('#info-tabContent .specifications dt')
                ->each(function (Crawler $dt) use ($product, $catalog) {
                    try {
                        $name = trim($dt->text());
                        $value = trim($dt->nextAll()->eq(0)->text());

                        $this->createProductCharWithParentCatalog($name, $value, $product, $catalog);

                    } catch (\Exception $e) {
                        $this->error('Ошибка парсинга характеристик: ' . $e->getMessage());
                        $this->error($e->getTraceAsString());

                        Log::channel('parser')->error($e->getMessage());
                        Log::channel('parser')->error($e->getTraceAsString());
                        exit();
                    }
                });

            //документы
            $download_types = ['Паспорт', 'Руководство', 'Сертификат'];
            $has_docs = $product_crawler->filter('.docs-list__table-body')->children()->count();
            if ($has_docs) {
                $product_crawler->filter('.docs-list__table-body')
                    ->children()
                    ->reduce(function ($node, $i) {
                        return ($i % 2) != 0; //четные исключаем (там только заголовок)
                    })
                    ->each(function (Crawler $elem) use ($download_types, $catalog, $product) {
                        //если файлов одного типа несколько
                        $elem->filter('.docs-list__table-row')
                            ->each(function (Crawler $down_row, $i) use ($download_types, $catalog, $product) {
                                $type = $down_row->children()->eq(0)->text(); //узнаем тип документа
                                if (in_array($type, $download_types)) {
                                    $url = $this->baseUrl . $down_row->filter('.docs-list__table-row')->children()->eq(4)->filter('a')->attr('href');
                                    $ext = $this->getExtensionFromSrc($url);
                                    $filename = $product->article . '_' . Text::translit($type);
                                    if ($i > 0) {
                                        $filename = $filename . '_' . ($i + 1);
                                    }
                                    $filename .= $ext;

                                    if (str_ends_with($url, 'pdf')) {
                                        if (!is_file(
                                            public_path(ProductDoc::UPLOAD_URL . $catalog->alias . '/' . $filename)
                                        )) {
                                            $this->downloadFile(
                                                $url,
                                                ProductDoc::UPLOAD_URL . $catalog->alias . '/',
                                                $filename
                                            );
                                        }

                                        $product_doc = ProductDoc::where('product_id', $product->id)
                                            ->where('file', $filename)->first();

                                        if (!$product_doc) {
                                            ProductDoc::create(
                                                [
                                                    'product_id' => $product->id,
                                                    'name' => $type,
                                                    'file' => $filename,
                                                    'order' => $i
                                                ]
                                            );
                                        }
                                    }
                                }
                            });
                    });
            }

        } catch (\Exception $e) {
            $this->error('Ошибка parseProduct: ' . $e->getMessage());
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

    public function test_product_list()
    {
        $html = file_get_contents(public_path('test/catalog_ridan.html'));

//        $productPage = $this->client->get('https://protection-chain.ru/catalog/tsepi-protivoskolzheniya/?PAGEN_1=2');
//        $html = $productPage->getBody()->getContents();

        $product_list_crawler = new Crawler($html);

        //описание раздела
//        $text = $product_list_crawler->filter('.product-card__card-content .product-card__block')->eq(2)->html();
        $image_url = $product_list_crawler->filter('.carousel-item.active')->attr('href');
        $image_url = $this->baseUrl . $image_url;
        dd($image_url);

        $links = [];
        $n = 0;
        $url = '';
        $article = '';
        $name = '';
        $product_list_crawler->filter('.series-products__body table tbody tr')
            ->each(function (Crawler $row, $i) use (&$links, &$n, &$url, &$article, &$name) {
                //первый tr - название/артикул/ссылка, второй - цена
                if ($i % 2 == 0) {
                    $url = $this->baseUrl . $row->filter('.link-primary')->attr('href');
                    $article = $row->filter('.link-primary')->text();
                    $name = $row->filter('.text-break')->text();
                }
                if ($i % 2 != 0) {
                    $price = $row->filter('.series-products__price span[data-product-price]')->text();
                    $price = str_replace(' ', '', $price);
                    $links[$n] = [$url, $article, $name, $price];
                    $n++;
                }
            });
        dump($links);
    }

    public function test_product()
    {
        $html = file_get_contents(public_path('test/product_ridan.html'));
        $product_crawler = new Crawler($html);

        //изображения
//        $has_images = $product_crawler->filter('.carousel-item')->count();
//        $upload_url = '/test/img/';
//        if ($has_images) {
//            $product_crawler->filter('.carousel-item')->each(function (Crawler $img, $i) use ($upload_url) {
//               $url = $this->baseUrl . $img->attr('href');
//               $name = Text::translit('Насос RWS 20-40S 130') . '_' . $i;
//
//               $this->downloadJpgFile($url, $upload_url, $name);
//            });
//        }

        //характеристики
//        $product_crawler->filter('#info-tabContent .specifications dt')->each(function (Crawler $dt) {
//            $name = $dt->text();
//            $val = $dt->nextAll()->eq(0)->text();
//
//            $this->info($name . ' - ' . $val);
//        });

        //документы
        $download_types = ['Паспорт', 'Руководство', 'Сертификат'];
        $has_docs = $product_crawler->filter('.docs-list__table-body')->children()->count();
        if ($has_docs) {
            $product_crawler->filter('.docs-list__table-body')
                ->children()
                ->reduce(function ($node, $i) {
                    return ($i % 2) != 0; //четные исключаем (там только заголовок)
                })
                ->each(function (Crawler $elem) use ($download_types) {
                    //если файлов одного типа несколько
                    $elem->filter('.docs-list__table-row')->each(function (Crawler $down_row, $i) use ($download_types) {
                        $type = $down_row->children()->eq(0)->text(); //узнаем тип документа
                        if (in_array($type, $download_types)) {
                            $upload_url = '/test/docs/';
                            $url = $this->baseUrl . $down_row->filter('.docs-list__table-row')->children()->eq(4)->filter('a')->attr('href');
                            $filename = Text::translit($type);
                            if ($i > 0) {
                                $filename = $filename . '_' . $i;
                            }
//                            dump($url);

                            $this->downloadFile($url, $upload_url, $filename);
                        }
                    });
                });
        }
    }
}
