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
//        $this->test_product();
//        exit();

        foreach ($this->catalogList() as $catName => $catUrl) {
            $this->parseCatalog($catName, $catUrl, 7);
        }

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
        $this->info('Парсим раздел: ' . $categoryName . ' (' . $categoryUrl . ')');
        $catalog = $this->getCatalogByName($categoryName, $parent);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $catalog_crawler = new Crawler($html);

            //парсим подразделы, если есть
            $hasSections = $catalog_crawler->filter('.list.items .item')->count();
            if ($hasSections) {
                $catalog_crawler->filter('.list.items .item')
                    ->each(
                        function (Crawler $section) use ($catalog) {
                            $name = trim($section->filter('.name a')->text());
                            $url = $this->baseUrl . $section->filter('.name a')->attr('href');
                            $this->parseCatalog($name, $url, $catalog->id);
                        }
                    );
            }

            //парсим товары
            $hasProducts = $catalog_crawler->filter('.item_block')->count();
            if ($hasProducts) {
                $catalog_crawler->filter('.item_block')->each(
                    function (Crawler $item) use ($catalog) {
                        $name = trim($item->filter('.item-title a span')->text());
                        $url = $this->baseUrl . $item->filter('.item-title a')->attr('href');

                        $product = Product::where('parse_url', $url)->first();
                        if (!$product) {
                            $this->parseProduct($catalog, $name, $url);
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

    public function parseProduct(Catalog $catalog, $name, $url)
    {
        $this->info('Страница с товарами: ' . $name . ' (' . $url . ')');

        try {
            $res = $this->client->get($url);
            $html = $res->getBody()->getContents();
            $product_crawler = new Crawler($html); //products page from url

            //описание товара
            $has_text = $product_crawler->filter('.descr-outer-wrapper .detail_text')->count();
            $text = '';
            if ($has_text) {
                $text = $product_crawler->filter('.descr-outer-wrapper .detail_text')->html();

                $text_crawler = new Crawler($text);

                $has_images = $text_crawler->filter('img')->count();
                $imgSrc = [];
                $imgArr = [];
                if ($has_images) {
                    $upload_path = '/test/catalog/text/';
                    $text_crawler->filter('img')
                        ->each(
                            function (Crawler $image, $i) use ($upload_path, &$imgSrc, &$imgArr) {
                                $raw_url = $image->attr('data-src');
                                $url = $this->baseUrl . $raw_url;
                                $arr = explode('/', $url);
                                $file_name = array_pop($arr);
                                $file_name = str_replace('%20', '_', $file_name);

                                if ($this->checkIsImageJpg($file_name)) {
                                    if (!is_file(public_path($upload_path . $file_name))) {
                                        $this->downloadJpgFile($url, $upload_path, $file_name);
                                    }

                                    $imgSrc[] = $raw_url;
                                    $imgArr[] = $upload_path . $file_name;
                                }
                            }
                        );
                }
                $text = $this->getUpdatedTextWithNewImagesWellMix($text, $imgSrc, $imgArr);
            }

            //в скрипте находятся все данные по товарам на странице
            $js = $product_crawler->filter('.sku_props')->children()->last()->text();
            $start = strpos($js, 'OFFERS') + 8;
            $end = strpos($js, 'OFFER_SELECTED') - 2;
            $length = $end - $start;
            $res = substr($js, $start, $length); //вырезаем только товары
            $res_quotes = str_replace("'", "\"", $res); //меняем кавычки, чтобы json читался без ошибок

            $obj = json_decode($res_quotes, true);

            $prods = [];
            foreach ($obj as $n => $item) {
                $chars = $item['DISPLAY_PROPERTIES'];
                $chars_all = [];
                foreach ($chars as $elem) {
                    $name = html_entity_decode($elem['NAME']);
                    $val = html_entity_decode($elem['VALUE']);
                    $chars_all[] = [$name, $val];
                }

                $images = $item['SLIDER'];
                $images_all = [];
                foreach ($images as $image) {
                    $images_all[] = $this->baseUrl . $image['SRC'];
                }

                $prods[] = [
                    'id' => $item['ID'],
                    'name' => html_entity_decode($item['NAME']),
                    'article' => $item['DISPLAY_PROPERTIES_CODE']['ARTICLE']['VALUE'],
                    'price' => $item['PRICE']['VALUE'],
                    'chars' => $chars_all,
                    'images' => $images_all,
                    'product_count' => $item['COUNT_IN_ALL_WAREHOUSES'],
                    'parse_url' => $this->baseUrl . $item['URL']
                ];
            }

            //общие документы - добавляем к каталогу
            $has_common_docs = $product_crawler->filter('.files_block')->eq(0)->count();
            if ($has_common_docs) {
                $product_crawler->filter('.files_block')->eq(0)->each(
                    function (Crawler $block) use ($catalog) {
                        $block->filter('.row')->children()->each(
                            function (Crawler $file) use ($catalog) {
                                $name = trim($file->filter('.description a')->text());
                                $url = $this->baseUrl . $file->filter('.description a')->attr('href');

                                $catalog_doc = CatalogDoc::where('catalog_id', $catalog->id)
                                    ->where('name', $name)->first();
                                if (!$catalog_doc) {
                                    $ext = $this->getExtensionFromSrc($url);
                                    $file_name = Text::translit($name) . $ext;
                                    $upload_path = CatalogDoc::UPLOAD_URL . $catalog->alias . '/';

                                    $res = $this->downloadFile($url, $upload_path, $file_name);

                                    if ($res) {
                                        CatalogDoc::create(
                                            [
                                                'catalog_id' => $catalog->id,
                                                'name' => $name,
                                                'file' => $file_name,
                                                'order' => CatalogDoc::where('catalog_id', $catalog->id)->max('order') + 1
                                            ]
                                        );
                                    }
                                }
                            }
                        );
                    }
                );
            }

            //все документы товаров - к каждому товару свой
            $products_docs = [];
            $has_products_docs = $product_crawler->filter('.files_block')->eq(1)->count();
            if ($has_products_docs) {
                $product_crawler->filter('.files_block')->eq(1)->each(
                    function (Crawler $block) use (&$products_docs) {
                        $block->filter('.row')->children()->each(
                            function (Crawler $file) use (&$products_docs) {
                                $name = trim($file->filter('.description a')->text());
                                $url = $this->baseUrl . $file->filter('.description a')->attr('href');

                                $products_docs[] = ['name' => $name, 'url' => $url];
                            }
                        );
                    }
                );
            }

            //проверим, что количество товаров = количеству документов, чтобы не перепутать товары-документы
            if (count($products_docs) == count($prods)) {
                //заносим товары в БД
                foreach ($prods as $count => $json_prod) {
                    $product = Product::where('parse_url', $json_prod['parse_url'])->first();
                    if (!$product) {
                        $data['name'] = $json_prod['name'];
                        $data['catalog_id'] = $catalog->id;
                        $data['alias'] = Text::translit($json_prod['name']);
                        $data['h1'] = $json_prod['name'];
                        $data['title'] = $json_prod['name'];
                        $data['price'] = $json_prod['price'];
                        $data['article'] = $json_prod['article'];
                        $data['text'] = $text;
                        $data['published'] = 1;
                        $data['parse_url'] = $json_prod['parse_url'];
                        $data['order'] = Product::where('catalog_id', $catalog->id)->max('order') + 1;

                        $product = Product::create($data);
                        $this->info('Новый товар: ' . $product->name . ' (id=' . $product->id . ')');

                        //создаем характеристики
                        if (count($json_prod['chars'])) {
                            foreach ($json_prod['chars'] as $i => $values) {
                                $name = $values[0];
                                $value = $values[1];
                                $this->createProductCharWithParentCatalog($name, $value, $product, $catalog);
                            }
                        }

                        //скачаем изображения товара
                        if (count($json_prod['images'])) {
                            foreach ($json_prod['images'] as $i => $url) {
                                $ext = $this->getExtensionFromSrc($url);
                                $file_name = $product->article . '_' . $i;
                                $file_name .= $ext;
                                $this->uploadProductImage($url, $file_name, $product);
                            }
                        }

                        //загружаем соответствующий документ из массива
                        $url = $products_docs[$count]['url'];
                        $name = $products_docs[$count]['name'];
                        $ext = $this->getExtensionFromSrc($url);
                        $file_name = $product->alias .$ext;

                        $this->uploadProductDoc($url, $file_name, $name, $product);
                    }
                }
            } else {
                $this->error('Количество док-ов и товаров не равно!');
                exit();
            }
            exit();
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

    public function test_catalog()
    {
        $html = file_get_contents(public_path('test/catalog_well.html'));

//        $productPage = $this->client->get('https://protection-chain.ru/catalog/tsepi-protivoskolzheniya/?PAGEN_1=2');
//        $html = $productPage->getBody()->getContents();

        $catalog_crawler = new Crawler($html);

        //parse sections
        $hasSections = $catalog_crawler->filter('.list.items .item')->count();
        if ($hasSections) {
            $catalog_crawler->filter('.list.items .item')->each(
                function (Crawler $section) {
                    $name = trim($section->filter('.name a')->text());
                    $url = $this->baseUrl . $section->filter('.name a')->attr('href');

                    $this->info($name);
                }
            );
        }

        $hasDesc = $catalog_crawler->filter('.group_description_block')->count();
//        if ($hasDesc) {
//            $desc = $catalog_crawler->filter('.group_description_block')->html();
//        }


        //parse products
//        $hasProducts = $catalog_crawler->filter('.item_block')->count();
//        if ($hasProducts) {
//            $catalog_crawler->filter('.item_block')->each(function (Crawler $item) {
//                $name = trim($item->filter('.item-title a span')->text());
//                $url = $this->baseUrl . $item->filter('.item-title a')->attr('href');
//            });
//        }
    }

    public function test_product()
    {
        $html = file_get_contents(public_path('test/product_well.html'));
        $product_crawler = new Crawler($html);

        //name ??
//        $name = trim($product_crawler->filter('.page-top-main h1')->text());
//        dump($name);

        //description
//        $has_descr = $product_crawler->filter('.descr-outer-wrapper .detail_text')->count();
//        if ($has_descr) {
//            $descr = $product_crawler->filter('.descr-outer-wrapper .detail_text')->html();
//
//            $descr_crawler = new Crawler($descr);
//
//            $has_images = $descr_crawler->filter('img')->count();
//            $imgSrc = [];
//            $imgArr = [];
//            if ($has_images) {
//                $upload_path = '/test/catalog/text/';
//                $descr_crawler->filter('img')
//                    ->each(
//                        function (Crawler $image, $i) use ($upload_path, &$imgSrc, &$imgArr) {
//                            $raw_url = $image->attr('data-src');
//                            $url = $this->baseUrl . $raw_url;
//                            $arr = explode('/', $url);
//                            $file_name = array_pop($arr);
//                            $file_name = str_replace('%20', '_', $file_name);
//
//                            if ($this->checkIsImageJpg($file_name)) {
//                                if (!is_file(public_path($upload_path . $file_name))) {
//                                    $this->downloadJpgFile($url, $upload_path, $file_name);
//                                }
//
//                                $imgSrc[] = $raw_url;
//                                $imgArr[] = $upload_path . $file_name;
//                            }
//                        }
//                    );
//            }
//            $text = $this->getUpdatedTextWithNewImagesWellMix($descr, $imgSrc, $imgArr);
//            dd($text);
//        }


        //общие документы - добавляем к каталогу
        $has_common_docs = $product_crawler->filter('.files_block')->eq(0)->count();
        if ($has_common_docs) {
            $product_crawler->filter('.files_block')->eq(0)->each(
                function (Crawler $block) {
                    $block->filter('.row')->children()->each(
                        function (Crawler $file) {
                            $name = trim($file->filter('.description a')->text());
                            $url = $this->baseUrl . $file->filter('.description a')->attr('href');
                        }
                    );
                }
            );
        }

        //все документы товаров - к каждому товару свой
        $products_docs = [];
        $has_products_docs = $product_crawler->filter('.files_block')->eq(1)->count();
        if ($has_products_docs) {
            $product_crawler->filter('.files_block')->eq(1)->each(
                function (Crawler $block) use (&$products_docs) {
                    $block->filter('.row')->children()->each(
                        function (Crawler $file) use (&$products_docs) {
                            $name = trim($file->filter('.description a')->text());
                            $url = $this->baseUrl . $file->filter('.description a')->attr('href');

                            $products_docs[] = ['name' => $name, 'url' => $url];
                        }
                    );
                }
            );
        }
        dd($products_docs);

        exit();

        $js = $product_crawler->filter('.sku_props')->children()->last()->text();
        $start = strpos($js, 'OFFERS') + 8;
        $end = strpos($js, 'OFFER_SELECTED') - 2;
        $length = $end - $start;
        $res = substr($js, $start, $length);
        $res_quotes = str_replace("'", "\"", $res);

        $obj = json_decode($res_quotes, true);

        $prods = [];
        foreach ($obj as $n => $item) {
            $chars = $item['DISPLAY_PROPERTIES'];
            $chars_all = [];
            foreach ($chars as $elem) {
                $name = html_entity_decode($elem['NAME']);
                $val = html_entity_decode($elem['VALUE']);
                $chars_all[] = [$name, $val];
            }

            $images = $item['SLIDER'];
            $images_all = [];
            foreach ($images as $image) {
                $images_all[] = $this->baseUrl . $image['SRC'];
            }

            $prods[] = [
                'id' => $item['ID'],
                'name' => html_entity_decode($item['NAME']),
                'article' => $item['DISPLAY_PROPERTIES_CODE']['ARTICLE']['VALUE'],
                'price' => $item['PRICE']['VALUE'],
                'chars' => $chars_all,
                'images' => $images_all,
                'in_stock' => $item['COUNT_IN_ALL_WAREHOUSES'] == 0 ? 0 : 1,
                'parse_url' => $this->baseUrl . $item['URL']
            ];


            dd($prods);

            exit();
        }
        var_dump($prods);
    }

    public
    function getUpdatedTextWithNewImagesWellMix(
        string $text,
        array $imgSrc,
        array $imgArr
    ): string {
        if ($text == null) {
            return '';
        }
        if (count($imgArr) == 0) {
            return $text;
        }

        $start = 0;
        foreach ($imgArr as $url) {
            $a = mb_stripos($text, '<img', $start);
            $b = mb_stripos($text, '>', $a + 4) + 1;
            $start = $b;
            $old_img = mb_substr($text, $a, $b - $a);
            $new_img = '<img src="' . $url . '">';
            $text = str_replace($old_img, $new_img, $text);
        }

        return $text;
    }
}
