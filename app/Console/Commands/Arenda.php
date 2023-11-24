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

class Arenda extends Command {

    use ParseFunctions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:arenda';
    private $basePath = ProductImage::UPLOAD_URL . 'arenda/';
    public $baseUrl = 'https://xn--80ac5aetf.xn--p1ai/';
    public $client;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсим аренду спецтехники';

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

        $p = Product::whereName('Лоток лестничный крестообразный НЛ-К')->first();
        $this->info(($p->id));
        exit();

        $categoryUrl = 'https://xn--80ac5aetf.xn--p1ai/arenda-tehniki/';
        $res = $this->client->get($categoryUrl);
        $html = $res->getBody()->getContents();
        $catalogCrawler = new Crawler($html); //catalog page from url

        $catalogCrawler->filter('.c_item')
            ->reduce(function (Crawler $none, $i) {
                return ($i == 14); //max 14
            })
            ->each(function (Crawler $sub) {
                $url = $sub->filter('.c_item_title')->first()->attr('href');
                $name = trim($sub->filter('.c_item_title')->first()->text());
                $catalog = $this->getCatalogByName($name, 7);

                if (!$catalog->image && $sub->filter('.c_item_img img')->first()->count() != 0) {
                    $src = $sub->filter('.c_item_img img')->first()->attr('src');
                    $uploadPath = Catalog::UPLOAD_URL;
                    $ext = $this->getExtensionFromSrc($src);
                    $fileName = $catalog->alias . $ext;
                    $res = $this->downloadJpgFile($src, $uploadPath, $fileName);
                    if ($res) {
                        $catalog->image = $fileName;
                        $catalog->save();
                    }
                }
                //парсим товары
                try {
                    $this->parseListProductArenda($catalog, $url);
                } catch (\Exception $e) {
                    $this->error('Error Parse Products from section: ' . $e->getMessage());
                    $this->error('See line: ' . $e->getLine());
                    exit();
                }
            });

        $this->info('The command was successful!');
    }

    public function parseCategoryArenda($categoryName, $categoryUrl, $parentId) {
        $this->info($categoryName . ' => ' . $categoryUrl);
        $catalog = $this->getCatalogByName($categoryName, $parentId);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $sectionCrawler = new Crawler($html); //section page from url

            if ($sectionCrawler->filter('.c_item')->count() != 0) {
                $sectionCrawler->filter('.c_item')
                    ->each(function (Crawler $sectionInnerCrawler) use ($catalog) {
                        $url = $sectionInnerCrawler->filter('.c_item_title')->first()->attr('href');
                        $name = trim($sectionInnerCrawler->filter('.c_item_title')->first()->text());

                        if (!$catalog->image && $sectionInnerCrawler->filter('img')->first()->count() != 0) {
                            $src = $sectionInnerCrawler->filter('img')->first()->attr('data-src');
                            $uploadPath = Catalog::UPLOAD_URL;
                            $ext = $this->getExtensionFromSrc($src);
                            $fileName = $catalog->alias . $ext;
                            $res = $this->downloadJpgFile($src, $uploadPath, $fileName);
                            if ($res) {
                                $catalog->image = $fileName;
                                $catalog->save();
                            }
                        }

                        $this->parseCategoryLights($name, $url, $catalog->id);
                    });
            } else {
                //парсим товары
                try {
                    $this->parseListProductArenda($catalog, $categoryUrl);
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

    public function parseListProductArenda($catalog, $categoryUrl) {
        $this->info('Parse products from: ' . $catalog->name);
        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $crawler = new Crawler($html); //products page from url
            $uploadPath = $this->basePath . $catalog->alias . '/';

            if (!$catalog->description && $crawler->filter('.cols_page_right span')->first()->count() != 0) {
                $catalog->description = $crawler->filter('.cols_page_right span')->first()->text();
                $catalog->save();
            }

            $crawler->filter('.category_item')
//                    ->reduce(function (Crawler $none, $i) {return ($i < 1);})
                ->each(function (Crawler $node, $n) use ($catalog, $uploadPath) {
                    $data = [];
                    try {
                        $url = $node->filter('link')->first()->attr('href');
                        $data['name'] = trim($node->filter('.category_item_img img')
                            ->first()->attr('title')); //имя из title картинки, потому что кривая верстка для h3.category_item_title

                        if ($node->filter('.cost_field')
                                ->children('p')->first()
                                ->children('span')->count() == 2) {
                            $data['price'] = $node->filter('.cost_field')
                                ->children('p')->first()
                                ->children('span')->first()->text();
                            $data['price'] = str_replace(' ', null, $data['price']);

                        } else {
                            $rawStr = $node->filter('.cost_field')
                                ->children('p')->first()
                                ->children('span')->first()->text();
                            $data['price'] = preg_replace("/[^,.0-9]/", null, $rawStr);
                            $rawMeasure = explode('/', $rawStr);
                            $data['measure'] = $rawMeasure[1];
                        }

                        $data['in_stock'] = 1;

                        $this->info(++$n . ') ' . $data['name']);
                        $product = Product::whereParseUrl($url)->first();
                        if (!$product) {
                            $data['h1'] = $data['name'];
                            $data['title'] = $data['name'];
                            $data['alias'] = Text::translit($data['name']);

                            if ($node->filter('.cost_field')
                                    ->children('p')->first()
                                    ->children('span')->count() == 2) {
                                $rawMeasure = $node->filter('.cost_field')
                                    ->children('p')->first()
                                    ->children('span')->last()->text();
                                $rawMeasure = explode('/', $rawMeasure);
                                $data['measure'] = $rawMeasure[1];
                            } else {
                                $rawStr = $node->filter('.cost_field')
                                    ->children('p')->first()
                                    ->children('span')->first()->text();
                                $rawMeasure = explode('/', $rawStr);
                                $data['measure'] = $rawMeasure[1];
                            }

                            $rowMinTime = $node->filter('.cost_field')
                                ->children('p')->first()->text();
                            $data['min_hours'] = $this->extractMinHours($rowMinTime);

                            $productPage = $this->client->get($url);
                            $productHtml = $productPage->getBody()->getContents();
                            $productCrawler = new Crawler($productHtml); //product page

                            //описание
                            $data['text'] = $productCrawler->filter('.description_field')->first()->html();

                            $newProd = Product::create(array_merge([
                                'catalog_id' => $catalog->id,
                                'parse_url' => $url,
                                'published' => 1,
                                'order' => $catalog->products()->max('order') + 1,
                            ], $data));

                            //характеристики
                            $rawChars = $productCrawler->filter('.teh_chars_content')->first()->html();
                            $chars = $this->extractChars($rawChars);

                            foreach ($chars as $char) {
                                [$name, $value] = $char;
                                $currentChar = Char::whereName($name)->first();
                                if (!$currentChar) {
                                    $currentChar = Char::create([
                                        'name' => $name
                                    ]);
                                }
                                ProductChar::create([
                                    'product_id' => $newProd->id,
                                    'char_id' => $currentChar->id,
                                    'value' => $value,
                                    'order' => ProductChar::where('product_id', $newProd->id)->max('order') + 1,
                                ]);
                            }

                            //сохраняем 1 изображения товара
                            if ($productCrawler->filter('.cp_photo_main img')->first()->count() != 0) {
                                $imageSrc = $productCrawler->filter('.cp_photo_main img')->first()->attr('src');
                                $ext = $this->getExtensionFromSrc($imageSrc);
                                $fileName = 'arenda_' . $newProd->id . $ext;
                                $res = $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
                                if ($res) {
                                    ProductImage::create([
                                        'product_id' => $newProd->id,
                                        'image' => $uploadPath . $fileName,
                                        'order' => ProductImage::where('product_id', $newProd->id)->max('order') + 1,
                                    ]);
                                }
                            }

                            //сохраняем 2 изображения товара
                            if ($productCrawler->filter('.cp_gallery .gallery-icon img')->first()->count() != 0) {
                                $imageSrc = $productCrawler->filter('.cp_gallery .gallery-icon img')->first()->attr('src');
                                $ext = $this->getExtensionFromSrc($imageSrc);
                                $fileName = 'arenda_' . $newProd->id . '_2' . $ext;
                                $res = $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
                                if ($res) {
                                    ProductImage::create([
                                        'product_id' => $newProd->id,
                                        'image' => $uploadPath . $fileName,
                                        'order' => ProductImage::where('product_id', $newProd->id)->max('order') + 1,
                                    ]);
                                }
                            }
                            sleep(rand(0, 2));
                        } else {
                            $product->update($data);
                            $product->save();
                        }
                    } catch (\Exception $e) {
                        $this->warn('error parse product: ' . $e->getMessage());
                        $this->warn('see line: ' . $e->getLine());
                        exit();
                    }
                });

            //проход по страницам
//            if ($crawler->filter('.facetwp-page.next')->count() != 0) {
//                $nextUrl = $crawler->filter('.facetwp-page.next')->attr('href');
//                $this->info('parse next url: ' . $nextUrl);
//                $this->parseListProductArenda($catalog, $nextUrl);
//            }

        } catch (GuzzleException $e) {
            $this->error('Error Parse Product: ' . $e->getMessage());
            $this->error('See: ' . $e->getLine());
        }
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
                if (($char->filter('td')->first()->count() != 0)) {
                    $name = $char->filter('td')->first()->text();
                    $name = $this->deleteCharFromEnd($name);
                    $value = $char->filter('td')->text();
                }

                if ($name) {
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

    public function extractMinHours(string $str): ?string {
        $f = mb_strripos($str, 'Минимальное время заказа:');
        if (!$f) return null;

        $substr = mb_substr($str, $f + 26, 9);

        return preg_replace("/[^0-9]/", null, $substr);
    }


    public function extractChars(string $str): array {
        if(str_contains($str,'<ul>')) {
            $newStr = str_replace(['<ul>', '</ul>', '<li>'], null, $str);
            $arr =  array_map('trim', explode('</li>', $newStr));
        } else {
            $newStr = str_replace(['<p>', '</p>'], null, $str);
            $arr = array_map('trim', explode('<br>', $newStr));
        }
        $chars = [];
        foreach ($arr as $row) {
            if (stripos($row, ':')) {
                $chars[] = array_map('trim', explode(':', $row));
            } else {
                $chars[] = [trim($row), ''];
            }
        }
        return $chars;
    }

    public function test() {
        $html = file_get_contents(public_path('/test/test-cat.html'));
        $crawler = new Crawler($html); //products page from url

        $txt = '';
        $imgSrc = [];
        $imgArr = [];
        $crawler->filter('#primary')->nextAll()->each(function (Crawler $cr) use (&$txt, &$imgArr, &$imgSrc) {
            if ($cr->filter('img')->count() != 0) {
                $cr->filter('img')->each(function (Crawler $img) use (&$txt, &$imgArr, &$imgSrc) {
                    $url = $img->attr('src');
                    $this->info('download: ' . $url);
                    $imgSrc[] = $url;
                    $ext = $this->getExtensionFromSrc($url);
                    $imgArr[] = Catalog::UPLOAD_URL . md5(uniqid(rand(), true)) . '_' . time() . $ext;;
                });
            }
            $txt .= $cr->html();
        });

        $res = $this->getUpdatedTextWithNewImages($txt, $imgSrc, $imgArr);
        print_r($res);

        $crawler->filter('li.product-col')
//                    ->reduce(function (Crawler $none, $i) {return ($i < 3);})
            ->each(function (Crawler $node, $n) {
                $data = [];
                try {
                    $url = $this->baseUrl . $node->filter('a.product-loop-title')->first()->attr('href');
                    $data['name'] = trim($node->filter('h3.woocommerce-loop-product__title')->first()->text());
                    $rawPrice = $node->filter('span.woocommerce-Price-amount.amount')->first()->text();
                    $data['price'] = preg_replace("/[^,.0-9]/", null, $rawPrice);
                    $data['price'] = $this->replaceFloatValue($data['price']);
                    $data['in_stock'] = 1;
                    if (!$data['price']) $data['in_stock'] = 0;

                    $this->info(++$n . ') ' . $data['name']);
                    $product = Product::whereParseUrl($url)->first();
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

                    //характеристики
//                    if ($productCrawler->filter('table.woocommerce-product-attributes')->count() != 0) {
//                        $productCrawler->filter('.table.woocommerce-product-attributes tr')->each(function (Crawler $char) {
//                            $name = $char->filter('.woocommerce-product-attributes-item__label')->first()->text();
//                            if ($char->filter('.woocommerce-product-attributes-item__value a')->count() != 0) {
//                                $value = trim($char->filter('.woocommerce-product-attributes-item__value a')->first()->text());
//                            } else {
//                                $value = trim($char->filter('.woocommerce-product-attributes-item__value')->first()->text());
//                            }
//
//                            $this->info($name . ' : ' . $value);
//                        });
//                    }

                } catch (\Exception $e) {
                    $this->warn('error parse product: ' . $e->getMessage());
                    $this->warn('see line: ' . $e->getLine());
                    exit();
                }
            });
    }
}
