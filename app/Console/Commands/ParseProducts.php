<?php

namespace App\Console\Commands;

use App\Traits\ParseFunctions;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\CatalogFilter;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductChar;
use Fanky\Admin\Models\ProductImage;
use Fanky\Admin\Text;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\DomCrawler\Crawler;

class ParseProducts extends Command
{
    use ParseFunctions;

    protected $signature = 'parse';
    protected $description = 'Parsing site https://gremir.ru/';
    public $client;

    public $baseUrl = 'https://gremir.ru';

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

        foreach ($this->catalogList() as $catName => $catUrl) {
            $this->parseCatalog($catName, $catUrl);
        }

        $this->info('The command was successful!');
    }

    public function catalogList()
    {
        return [
            'Фланцы' => 'https://gremir.ru/flantsy/'
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

            if ($catalog_crawler->filter('.category-desc')->count() > 0) {
                $text = $catalog_crawler->filter('.category-desc')->html();
                $catalog->text = $text;
                $catalog->save();
            }

            if ($catalog_crawler->filter('.sub-links')->count() > 0) {
                $catalog_crawler->filter('.sub-links li')
//                    ->reduce(function (Crawler $a, $i) {
//                        return ($i == 0);
//                    })
                    ->each(
                        function (Crawler $item, $i) use ($catalog) {
                            $url = $this->baseUrl . $item->filter('a')->attr('href');
                            $name = trim($item->filter('.cat-name')->text());
                            $this->parseCatalog($name, $url, $catalog->id);
                        }
                    );
            } else {
                if ($parent != 0) {
                    //парсим список товаров в разделе
                    $catalog_crawler->filter('.prod-list.prod-list-top tr')
                        ->reduce(
                            function (Crawler $a, $i) {
                                return ($i == 0);
                            }
                        )
                        ->each(
                            function (Crawler $list_item, $i) use ($catalog) {
                                $data = [];

                                $name = trim($list_item->filter('.list2-title')->text());
                                $url = $this->baseUrl . $list_item->filter('.list2-title')->attr('href');

                                if ($list_item->filter('.product-count--outstock')->count() > 0) {
                                    $data['product_count'] = 'Под заказ'; // наличие
                                } elseif ($list_item->filter('.product-count--instock')->count() > 0) {
                                    $product_count_text = $list_item->filter('.product-count--instock .nowrap')->text();
                                    $data['product_count'] = preg_replace(
                                        "/[^0-9]/",
                                        '',
                                        $product_count_text
                                    ); // наличие
                                } else {
                                    $data['product_count'] = null;
                                }

                                $price_text = trim($list_item->filter('.td-price span')->text());
                                if ($price_text == 'по запросу') {
                                    $data['price'] = 'по запросу';
                                } else {
                                    $data['price'] = preg_replace("/[^0-9]/", '', $price_text); // цена
                                }

                                // если товар уже парсили, обновим цену/наличие и дальше
                                $product = Product::where('parse_url', $url)->first();
                                if ($product) {
                                    $product->update($data);
                                } else {
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
                }
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseCatalog: ' . $e->getMessage());
            $this->error('Строка: ' . $e->getLine());
        }
    }

    public function parseCatalogNextPage($nextUrl, Catalog $catalog)
    {
        try {
            $res = $this->client->get($nextUrl);
            $html = $res->getBody()->getContents();
            $catalog_crawler = new Crawler($html);

            //парсим список товаров в разделе
            $catalog_crawler->filter('.prod-list.prod-list-top tr')
                ->each(
                    function (Crawler $list_item, $i) use ($catalog) {
                        $data = [];

                        $name = trim($list_item->filter('.list2-title')->text());
                        $url = $this->baseUrl . $list_item->filter('.list2-title')->attr('href');

                        if ($list_item->filter('.product-count--outstock')->count() > 0) {
                            $data['product_count'] = 'Под заказ'; // наличие
                        } elseif ($list_item->filter('.product-count--instock')->count() > 0) {
                            $product_count_text = $list_item->filter('.product-count--instock .nowrap')->text();
                            $data['product_count'] = preg_replace(
                                "/[^0-9]/",
                                '',
                                $product_count_text
                            ); // наличие
                        } else {
                            $data['product_count'] = null;
                        }

                        $price_text = trim($list_item->filter('.td-price span')->text());
                        if ($price_text == 'по запросу') {
                            $data['price'] = 'по запросу';
                        } else {
                            $data['price'] = preg_replace("/[^0-9]/", '', $price_text); // цена
                        }

                        // если товар уже парсили, обновим цену/наличие и дальше
                        $product = Product::where('parse_url', $url)->first();
                        if ($product) {
                            $product->update($data);
                        } else {
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
            $this->error('Строка: ' . $e->getLine());
        }
    }

    public function parseProduct(Catalog $catalog, $name, $url)
    {
        $this->info('Парсим товар: ' . $name);

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
            $data['price'] = $product_crawler->filter('.product-price .price')->attr('data-price');

            // артикул
            if ($product_crawler->filter('.product-cart-top span.bold')->count()) {
                $data['article'] = trim($product_crawler->filter('.product-cart-top span.bold')->text());
            }

            // наличие
            if ($product_crawler->filter('.product-count--outstock')->count() > 0) {
                $data['product_count'] = 'Под заказ';
                $data['in_stock'] = 0;
            } elseif ($product_crawler->filter('.product-count--instock')->count() > 0) {
                $product_count_text = $product_crawler->filter('.product-count--instock .nowrap')->text();
                $data['product_count'] = preg_replace("/[^0-9]/", '', $product_count_text);
                $data['in_stock'] = 1;
            }

            $product = Product::create($data);

            //характеристики
            if ($product_crawler->filter('#product-features')->count() > 0) {
                $product_crawler->filter('#product-features tr')->each(
                    function (Crawler $tr, $i) use ($product) {
                        $name_text = trim($tr->filter('td.name')->text());
                        $name = preg_replace("/:/", '', $name_text);

                        $char = ProductChar::where('product_id', $product->id)->where('name', $name)->first();
                        if (!$char) {
                            $value = trim($tr->filter('td.value')->text());
                            ProductChar::create(
                                [
                                    'catalog_id' => $product->catalog_id,
                                    'product_id' => $product->id,
                                    'name' => $name,
                                    'value' => $value,
                                    'order' => ProductChar::where('product_id', $product->id)->max('order') + 1
                                ]
                            );
                        }
                        $cat_filter = CatalogFilter::where('catalog_id', $product->catalog_id)->where(
                            'name',
                            $name
                        )->first();
                        if (!$cat_filter) {
                            CatalogFilter::create(
                                [
                                    'catalog_id' => $product->catalog_id,
                                    'name' => $name,
                                    'order' => CatalogFilter::where('catalog_id', $product->catalog_id)->max(
                                            'order'
                                        ) + 1,
                                    'published' => 1
                                ]
                            );
                        }
                    }
                );
            }

            //изображения
            if ($product_crawler->filter('.imgs')->count() > 0) {
                // 1 изображение
                if ($product_crawler->filter('.imgs .image')->count() == 1) {
                    $url_raw = $product_crawler->filter('.imgs .image a')->filter('a')->attr('href');
                    $url = $this->baseUrl . $url_raw;
                    $url_arr = explode('.', $url_raw);
                    $ext = array_pop($url_arr);
                    $file_name = $data['article'] . '.' . $ext;
                    $this->uploadProductImage($url, $file_name, $product->id);
                }

                // больше 1 изображения
                if ($product_crawler->filter('.imgs .more-images .image')->count() > 1) {
                    $product_crawler->filter('.imgs .more-images .image')->each(
                        function (Crawler $image, $i) use ($product, $data) {
                            $url_raw = $image->filter('a')->attr('href');
                            $url = $this->baseUrl . $url_raw;
                            $url_arr = explode('.', $url_raw);
                            $ext = array_pop($url_arr);
                            $file_name = $data['article'] . '_' . ($i + 1) . '.' . $ext;
                            $this->uploadProductImage($url, $file_name, $product->id);
                        }
                    );
                }
            }
        } catch (\Exception $e) {
            $this->error('Ошибка parseProduct: ' . $e->getMessage());
            $this->error('Строка: ' . $e->getLine());
        }
    }

    public function uploadProductImage($url, $file_name, $product_id)
    {
        if (!is_file(public_path(ProductImage::UPLOAD_URL . $file_name))) {
            $this->downloadJpgFile($url, ProductImage::UPLOAD_URL, $file_name);
        }
        $img = ProductImage::where('image', $file_name)->first();
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
        $html = file_get_contents(public_path('test/product_list_2.html'));

//        $productPage = $this->client->get('https://protection-chain.ru/catalog/tsepi-protivoskolzheniya/?PAGEN_1=2');
//        $html = $productPage->getBody()->getContents();

        $product_list_crawler = new Crawler($html);

        $url = 'no';
        if ($product_list_crawler->filter('.menu-h')->count() > 0) {
            $last_li_class = $product_list_crawler->filter('.menu-h li a')->last()->attr('class');
            if ($last_li_class == 'inline-link') {
                $url = $this->baseUrl . $product_list_crawler->filter('.menu-h li a')->last()->attr('href');
            }
            dump($url);
        }

//        if ($product_list_crawler->filter('.sub-links')->count() > 0) {
//            $product_list_crawler->filter('.sub-links li')
//                ->reduce(function (Crawler $a, $i) {
//                    return ($i == 0);
//                })
//                ->each(function (Crawler $item, $i) {
//                    $url = $this->baseUrl . $item->filter('a')->attr('href');
//                    $name = trim($item->filter('.cat-name')->text());
//                });
//        }

//        $product_list_crawler->filter('.prod-list.prod-list-top tr')
//            ->reduce(
//                function (Crawler $a, $i) {
//                    return ($i == 0);
//                }
//            )
//            ->each(
//                function (Crawler $list_item, $i) {
//                    $name = trim($list_item->filter('.list2-title')->text());
//                    $url = $this->baseUrl . $list_item->filter('.list2-title')->attr('href');
//
//                    if ($list_item->filter('.product-count--outstock')->count() > 0) {
//                        $product_count = 'Под заказ'; // наличие
//                    } elseif ($list_item->filter('.product-count--instock')->count() > 0) {
//                        $product_count_text = $list_item->filter('.product-count--instock .nowrap')->text();
//                        $product_count = preg_replace("/[^0-9]/", '', $product_count_text); // наличие
//                    } else {
//                        $product_count = null;
//                    }
//
//
//                    $price_text = trim($list_item->filter('.td-price span')->text());
//                    if ($price_text == 'по запросу') {
//                        $price = 'по запросу';
//                    } else {
//                        $price = preg_replace("/[^0-9]/", '', $price_text); // цена
//                    }
//
//                    dump($product_count);
//                }
//            );
    }

    public function test_product()
    {
        $html = file_get_contents(public_path('test/product.html'));

        $product_crawler = new Crawler($html);
        $data = [];

        $price = $product_crawler->filter('.product-price .price')->attr('data-price');
        dump($price);
        exit();

        $article = trim($product_crawler->filter('.product-cart-top span.bold')->text());

        if ($product_crawler->filter('.product-count--outstock')->count() > 0) {
            $product_count = 'Под заказ'; // наличие
        } elseif ($product_crawler->filter('.product-count--instock')->count() > 0) {
            $product_count_text = $product_crawler->filter('.product-count--instock .nowrap')->text();
            $product_count = preg_replace("/[^0-9]/", '', $product_count_text); // наличие
        } else {
            $product_count = null;
        }


        //характеристики
        if ($product_crawler->filter('#product-features')->count() > 0) {
            $product_crawler->filter('#product-features tr')->each(
                function (Crawler $tr, $i) {
                    $name_text = trim($tr->filter('td.name')->text());
                    $name = preg_replace("/:/", '', $name_text);
                    $value = trim($tr->filter('td.value')->text());
                    dump($name . ' : ' . $value);
                }
            );
        }

        //изображения
//        if ($product_crawler->filter('.imgs')->count() > 0) {
//            // 1 изображение
//            if ($product_crawler->filter('.imgs .image')->count() == 1) {
//                $url = $this->baseUrl . $product_crawler->filter('.imgs .image a')->filter('a')->attr('href');
//            }
//
//            // больше 1 изображения
//            if ($product_crawler->filter('.imgs .more-images .image')->count() > 1) {
//                $product_crawler->filter('.imgs .more-images .image')->each(
//                    function (Crawler $image, $i) {
//                        $url = $this->baseUrl . $image->filter('a')->attr('href');
//                        dump($url);
//                    }
//                );
//            }
//        }
    }
}
