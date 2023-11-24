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

class Uteplitel extends Command {

    use ParseFunctions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:utep';
    private $basePath = ProductImage::UPLOAD_URL . 'utepliteli/';
    public $baseUrl = 'https://olympiya.su';
    public $client;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсим утеплители';

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
//        $this->parseProduct();

        foreach ($this->categoryList() as $categoryName => $categoryUrl) {
            $this->parseCategoryUtepliteli($categoryName, $categoryUrl, 257);
        }
        $this->info('The command was successful!');
    }

    public function categoryList(): array {
        return [
//            'Базальтовая теплоизоляция' => 'https://olympiya.su/produkciya/utepliteli/bazaltovaya-teploizolyacziya/',
//            'Минеральная теплоизоляция (кварц)' => 'https://olympiya.su/produkciya/utepliteli/mineralnaya-teploizolyacziya-kvarcz/',
//            'Экструдированный пенополистирол' => 'https://olympiya.su/produkciya/utepliteli/ekstrudirovannyj-penopolistirol/',
//            'Джут, пакля' => 'https://olympiya.su/produkciya/utepliteli/dzhut-paklya/',
            'Вспененный полиэтилен' => 'https://olympiya.su/produkciya/utepliteli/vspenennyj-polietilen/',
        ];
    }

    public function parseCategoryUtepliteli($categoryName, $categoryUrl, $parentId) {
        $this->info($categoryName . ' => ' . $categoryUrl);
        $catalog = $this->getCatalogByName($categoryName, $parentId);
        //парсим товары
        try {
            $this->parseListProductUtepliteli($catalog, $categoryUrl);
        } catch (\Exception $e) {
            $this->error('Error Parse Products from section: ' . $e->getMessage());
            $this->error('See line: ' . $e->getLine());
        }
    }

    public function parseListProductUtepliteli($catalog, $categoryUrl) {
        $this->info('Parse products from: ' . $catalog->name);
        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $crawler = new Crawler($html); //products page from url
            $uploadPath = $this->basePath . $catalog->alias . '/';

            $crawler->filter('.hill_wc_product_item')
//                    ->reduce(function (Crawler $none, $i) {return ($i < 3);})
                ->each(function (Crawler $node, $n) use ($catalog, $uploadPath) {
                    $data = [];
                    try {
                        $url = $node->filter('.hill-wocommerce-row a')->first()->attr('href');
                        $data['name'] = trim($node->filter('.woocommerce-loop-product__title')->first()->text());
                        $rawPrice = $node->filter('.woocommerce-Price-amount.amount')->first()->text();
                        $data['price'] = preg_replace("/[^,.0-9]/", null, $rawPrice);
                        $data['price'] = $this->deleteCharFromEnd($data['price']); //убрать точку в конце цены
                        $data['in_stock'] = 1;
                        if(!$data['price']) $data['in_stock'] = 0;
                        $data['measure'] = $node->filter('.unitprice')->first()->text();
                        $data['measure'] = substr($data['measure'], 1); //убрать / в начале

                        $this->info(++$n . ') ' . $data['name']);
                        $product = Product::whereParseUrl($url)->first();
                        if (!$product) {

                            if($node->filter('.product_sub_cat a')->first()->count() != 0) {
                                $data['manufacturer'] = $node->filter('.product_sub_cat a')->first()->text();
                            }
                            $data['h1'] = $data['name'];
                            $data['title'] = $data['name'];
                            $data['alias'] = Text::translit($data['name']);

                            $productPage = $this->client->get($url);
                            $productHtml = $productPage->getBody()->getContents();
                            $productCrawler = new Crawler($productHtml); //product page

                            //описание
                            if ($productCrawler->filter('#tab-description')->first()->count() != 0) {
                                $data['text'] = $productCrawler->filter('#tab-description')->first()->html();
                            }

                            $order = $catalog->products()->max('order') + 1;
                            $newProd = Product::create(array_merge([
                                'catalog_id' => $catalog->id,
                                'parse_url' => $url,
                                'published' => 1,
                                'order' => $order,
                            ], $data));

                            //характеристики
                            if ($productCrawler->filter('#tab-local_1 table')->first()->count() != 0) {
                                $productCrawler->filter('#tab-local_1 table tr')->each(function (Crawler $char) use ($newProd) {
                                    $name = $char->filter('td')->first()->text();
                                    $name = $this->deleteCharFromEnd($name);
                                    $value = $char->filter('td')->last()->text();

                                    $currentChar = Char::whereName($name)->first();
                                    if (!$currentChar) {
                                        $currentChar = Char::create([
                                            'name' => trim($name)
                                        ]);
                                    }
                                    ProductChar::create([
                                        'product_id' => $newProd->id,
                                        'char_id' => $currentChar->id,
                                        'value' => $value,
                                        'order' => ProductChar::where('product_id', $newProd->id)->max('order') + 1,
                                    ]);
                                });
                            }

                            //сохраняем изображения товара
                            if ($productCrawler->filter('img.iconic-woothumbs-images__image')->count() != 0) {
                                $productCrawler->filter('img.iconic-woothumbs-images__image')->each(function ($img, $n) use ($newProd, $catalog, $uploadPath) {
                                    if($n == 0) {
                                        $imageSrc = $img->attr('src');
                                        $ext = $this->getExtensionFromSrc($imageSrc);
                                        $fileName = md5(uniqid(rand(), true)) . '_' . time() . $ext;
                                        $res = $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
                                        if ($res) {
                                            ProductImage::create([
                                                'product_id' => $newProd->id,
                                                'image' => $uploadPath . $fileName,
                                                'order' => ProductImage::where('product_id', $newProd->id)->max('order') + 1,
                                            ]);
                                        }
                                    }
                                });
                            }
                            sleep(rand(0, 2));
                        } else {
                            $product->update($data);
                            $product->save();
                        }
                    } catch (\Exception $e) {
                        $this->warn('error: ' . $e->getMessage());
                        $this->warn('see line: ' . $e->getLine());
                    }
                });

            //проход по страницам
            if ($crawler->filter('.next.page-numbers')->count() != 0) {
                $nextUrl = $crawler->filter('.next.page-numbers')->attr('href');
                $this->info('parse next url: ' . $nextUrl);
                $this->parseListProductUtepliteli($catalog, $nextUrl);
            }

        } catch (GuzzleException $e) {
            $this->error('Error Parse Product: ' . $e->getMessage());
            $this->error('See: ' . $e->getLine());
        }
    }

    private $excludeChars = ['.', ':'];

    public function deleteCharFromEnd(string $price) {
        $n = strlen($price);
        if (!in_array($price[$n - 1], $this->excludeChars)) return $price;

        return substr($price, 0, $n - 1);
    }


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

}
