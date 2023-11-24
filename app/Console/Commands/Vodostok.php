<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\Char;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductCertificate;
use Fanky\Admin\Models\ProductChar;
use Fanky\Admin\Models\ProductImage;
use Fanky\Admin\Text;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic;
use SiteHelper;
use Symfony\Component\DomCrawler\Crawler;
use SVG\SVG;
use App\Traits\ParseFunctions;

class Vodostok extends Command {

    use ParseFunctions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:vodo';
    private $basePath = ProductImage::UPLOAD_URL . 'vodostok/';
    public $baseUrl = 'https://www.grandline.ru';
    public $client;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсим водосток';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->client = new Client([
            'headers' => ['User-Agent' => Arr::random($this->userAgents)],
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        foreach ($this->categoryList() as $categoryName => $categoryUrl) {
            $this->parseCategoryVodostok($categoryName, $categoryUrl, 4);
        }
        $this->info('The command was successful!');
    }

    public function categoryList(): array {
        return [
//            'Grand Line 125/90' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/grand-line-125-90/',
//            'Grand Line 150/100' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/grand-line-150-100/',
//            'Optima круглый 125/90' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/vodostok-optima-kruglyy-125-90/',
//            'Optima круглый 150/100' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/optima-kruglyj-150100/',
//            'Vortex прямоугольный' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/vodostok-optima-pryamougolnyy/',
//            'Vortex прямоугольный Matt' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/vortex-matt/',
//            'Vortex Lite' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/vortex-lite/',
//            'Vortex Lite Matt' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/vortex-lite-matt/',
            'Vortex Project' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/vortex-project/',
            'Vortex Mix' => 'https://www.grandline.ru/katalog/vodostok/metallicheskie-vodostochnie-sistemi/vortex-mix/',
        ];
    }

    public function parseCategoryVodostok($categoryName, $categoryUrl, $parentId) {
        $this->info($categoryName . ' => ' . $categoryUrl);
        $catalog = $this->getCatalogByName($categoryName, $parentId);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $sectionCrawler = new Crawler($html); //section page from url

            if ($sectionCrawler->filter('ul.topics-list li')->count() != 0) {
                $sectionCrawler->filter('ul.topics-list li')->each(function (Crawler $sectionInnerCrawler) use ($catalog) {
                    $url = $this->baseUrl . $sectionInnerCrawler->filter('a')->first()->attr('href');
                    $name = trim($sectionInnerCrawler->filter('.topic-item__title')->first()->text());
                    $this->parseCategoryVodostok($name, $url, $catalog->id);
                });
            } else {
                //парсим товары
                try {
                    $this->parseListProductVodostok($catalog, $categoryUrl);
                } catch (\Exception $e) {
                    $this->error('Error Parse Products from section: ' . $e->getMessage());
                    $this->error('See line: ' . $e->getLine());
                    exit();
                }
            }
        } catch (GuzzleException $e) {
            $this->error('Error Parse Sections: ' . $e->getMessage());
        }
    }

    public function parseListProductVodostok($catalog, $categoryUrl) {
        $this->info('Parse products from: ' . $catalog->name);
        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $crawler = new Crawler($html); //products page from url
            $uploadPath = $this->basePath . $catalog->alias . '/';

            $crawler->filter('.product-item')
//                    ->reduce(function (Crawler $none, $i) {return ($i < 3);})
                ->each(function (Crawler $node, $n) use ($catalog, $uploadPath) {
                    $data = [];
                    try {
                        $url = $this->baseUrl . $node->filter('.product-item__title a')->first()->attr('href');
                        $data['name'] = trim($node->filter('.product-item__title a')->first()->text());
                        $rawPrice = $node->filter('.product-item__price')->first()->text();
                        $data['price'] = preg_replace("/[^,.0-9]/", null, $rawPrice);
                        $data['in_stock'] = 1;
                        if(!$data['price']) $data['in_stock'] = 0;
                        $data['measure'] = $this->extractMeasureFromPrice($rawPrice);

                        $this->info(++$n . ') ' . $data['name']);
                        $product = Product::whereParseUrl($url)->first();
                        if (!$product) {
                            $data['h1'] = $data['name'];
                            $data['title'] = $data['name'];
                            $data['alias'] = Text::translit($data['name']);

                            $productPage = $this->client->get($url);
                            $productHtml = $productPage->getBody()->getContents();
                            $productCrawler = new Crawler($productHtml); //product page

                            //описание
                            if ($productCrawler->filter('.description')->first()->count() != 0) {
                                $data['text'] = $productCrawler->filter('.description')->first()->html();
                            }

                            $order = $catalog->products()->max('order') + 1;
                            $newProd = Product::create(array_merge([
                                'catalog_id' => $catalog->id,
                                'parse_url' => $url,
                                'published' => 1,
                                'order' => $order,
                            ], $data));

                            //характеристики
                            if ($productCrawler->filter('.full-specifications-list__item')->count() != 0) {
                                $productCrawler->filter('.full-specifications-list__item')->each(function (Crawler $char) use ($newProd) {
                                    $name = $char->filter('.specification-item__title')->first()->text();
                                    if($char->filter('.specification-item__value a')->count() != 0) {
                                        $value = trim($char->filter('.specification-item__value a')->first()->text());
                                    } else {
                                        $value = trim($char->filter('.specification-item__value')->first()->text());
                                    }

                                    $currentChar = Char::whereName($name)->first();
                                    if (!$currentChar) {
                                        $currentChar = Char::create([
                                            'name' => trim($name)
                                        ]);
                                    }
                                    ProductChar::create([
                                        'product_id' => $newProd->id,
                                        'char_id' => $currentChar->id,
                                        'value' => trim($value),
                                        'order' => ProductChar::where('product_id', $newProd->id)->max('order') + 1,
                                    ]);
                                });
                            }

                            //сохраняем изображения товара
                            if ($productCrawler->filter('.product .product-slider__img img')->count() != 0) {
                                $productCrawler->filter('.product .product-slider__img img')->each(function ($img, $n) use ($newProd, $catalog, $uploadPath) {
                                    $imageSrc = $img->attr('data-src');
                                    $ext = $this->getExtensionFromSrc($imageSrc);
                                    $fileName = 'vod_product_' . $newProd->id . '_' . $n . $ext;
                                    $res = $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
                                    if ($res) {
                                        ProductImage::create([
                                            'product_id' => $newProd->id,
                                            'image' => $uploadPath . $fileName,
                                            'order' => ProductImage::where('product_id', $newProd->id)->max('order') + 1,
                                        ]);
                                    }
                                });
                            }
                            sleep(rand(0, 2));
                        } else {
                            $product->update($data);
                            $product->save();
                        }
                    } catch (\Exception $e) {
                        $this->warn('error parse product: ' . $e->getMessage());
                        $this->warn('see line: ' . $e->getLine());
                    }
                });

            //проход по страницам
//            if ($crawler->filter('.next.page-numbers')->count() != 0) {
//                $nextUrl = $crawler->filter('.next.page-numbers')->attr('href');
//                $this->info('parse next url: ' . $nextUrl);
//                $this->parseListProductVodostok($catalog, $nextUrl);
//            }

        } catch (GuzzleException $e) {
            $this->error('Error Parse Product: ' . $e->getMessage());
            $this->error('See: ' . $e->getLine());
        }
    }

    private $excludeChars = ['.', ':'];

    public function parseProduct() {
        $url = 'https://olympiya.su/shop/utepliteli/bazaltovaya-teploizolyacziya/plity-teploizolyaczionnye-iz-mineralnoj-vaty-na-sinteticheskom-svyazuyushhem-izba-lajt-35kg-m3-1000h600h50h12-pl/';
        $productPage = $this->client->get($url);
        $productHtml = $productPage->getBody()->getContents();
        $productCrawler = new Crawler($productHtml); //product page


        $catalog = Catalog::where('name', 'Базальтовая теплоизоляция')->first();
        $product = Product::find(8588);
        $uploadPath = $this->basePath . $catalog->alias . '/';

        //характеристики
        if ($productCrawler->filter('#tab-local_1 table')->first()->count() != 0) {
            $productCrawler->filter('#tab-local_1 table tr')->each(function (Crawler $char) use ($product) {
                if(($char->filter('td')->first()->count() != 0)) {
                    $name = $char->filter('td')->first()->text();
                    $name = $this->deleteCharFromEnd($name);
                    $value = $char->filter('td')->text();
                }

                if($name) {
                    $currentChar = Char::whereName($name)->first();
                    if (!$currentChar) {
                        $currentChar = Char::create([
                            'name' => trim($name)
                        ]);
                    }
                    ProductChar::create([
                        'product_id' => $product->id,
                        'char_id' => $currentChar->id,
                        'value' => $value,
                        'order' => ProductChar::where('product_id', $product->id)->max('order') + 1,
                    ]);
                } else {
                    $this->error('Not find chars!');
                }

            });
        }

        //сохраняем изображения товара
//        if ($productCrawler->filter('img.iconic-woothumbs-images__image')->count() != 0) {
//            $productCrawler->filter('img.iconic-woothumbs-images__image')->each(function ($img) use ($product, $catalog, $uploadPath) {
//                $imageSrc = $img->attr('src');
//                $ext = $this->getExtensionFromSrc($imageSrc);
//                $fileName = md5(uniqid(rand(), true)) . '_' . time() . $ext;
//                $res = $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
//                if ($res) {
//                    ProductImage::create([
//                        'product_id' => $product->id,
//                        'image' => $uploadPath . $fileName,
//                        'order' => ProductImage::where('product_id', $product->id)->max('order') + 1,
//                    ]);
//                }
//            });
//        }

    }

    public function extractMeasureFromPrice(string $price): ?string {
        $f = strripos($price, '/');
        if(!$f) return null;

        return substr($price, $f+1);
    }

}
