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

class Tubes extends Command {

    use ParseFunctions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:tubes';
    private $basePath = ProductImage::UPLOAD_URL . 'tubes/';
    public $baseUrl = 'https://set-iset.ru/';
    public $client;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсим Трубы для строительства инженерных систем';

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
        $categoryUrl = 'https://set-iset.ru/shop/';
        $res = $this->client->get($categoryUrl);
        $html = $res->getBody()->getContents();
        $catalogCrawler = new Crawler($html); //catalog page from url

        //4 - Трубы ПЭ 100 питьевые ГОСТ 18599-2001
        $catalogCrawler->filter('.product-category.product-col')
            ->reduce(function (Crawler $none, $i) {
                return ($i > 17);
            })
            ->each(function (Crawler $sub) {
                $url = $this->baseUrl . $sub->filter('a')->first()->attr('href');
                $name = trim($sub->filter('h4.m-t-md.m-b-none')->first()->text());
                $catalog = $this->getCatalogByName($name, 6);

                if (!$catalog->image && $sub->filter('.thumb-info-wrapper.tf-none img')->first()->count() != 0) {
                    $src = $sub->filter('.thumb-info-wrapper.tf-none img')->first()->attr('src');
                    $uploadPath = Catalog::UPLOAD_URL;
                    $ext = $this->getExtensionFromSrc($src);
                    $fileName = $catalog->alias . $ext;
                    $res = $this->downloadJpgFile($src, $uploadPath, $fileName);
                    if ($res) {
                        $catalog->image = $fileName;
                        $catalog->save();
                    }
                }

                $this->parseCategoryTubes($name, $url, $catalog->id);
            });

        $this->info('The command was successful!');
    }

    public function parseCategoryTubes($categoryName, $categoryUrl, $parentId) {
        $this->info($categoryName . ' => ' . $categoryUrl);
        $catalog = $this->getCatalogByName($categoryName, $parentId);

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $sectionCrawler = new Crawler($html); //section page from url

            //текст раздела с картинками
            if (!$catalog->text && $sectionCrawler->filter('#primary')->nextAll()->count() != 0) {
                $text = '';
                $imgSrc = [];
                $imgArr = [];
                $uploadPath = Catalog::UPLOAD_URL;
                $sectionCrawler->filter('#primary')->nextAll()->each(function (Crawler $cr) use (&$text, &$imgArr, &$imgSrc, $uploadPath) {
                    if ($cr->filter('img')->count() != 0) {
                        $cr->filter('img')->each(function (Crawler $img) use (&$text, &$imgArr, &$imgSrc, $uploadPath) {
                            $url = $img->attr('src');
                            $ext = $this->getExtensionFromSrc($url);
                            $fileName = md5(uniqid(rand())) . '_' . time() . $ext;
                            $res = $this->downloadJpgFile($url, $uploadPath, $fileName);
                            if ($res) {
                                $imgSrc[] = $url;
                                $imgArr[] = $uploadPath . $fileName;
                            }
                        });
                    }
                    $text .= $cr->html();
                    $text .= '<br>';
                });
                $catalog->text = $this->getUpdatedTextWithNewImages($text, $imgSrc, $imgArr);
                $catalog->save();
            }

            if ($sectionCrawler->filter('.product-category.product-col')->count() != 0) {
                $sectionCrawler->filter('.product-category.product-col')->each(function (Crawler $sectionInnerCrawler) use ($catalog) {
                    $url = $this->baseUrl . $sectionInnerCrawler->filter('a')->first()->attr('href');
                    $name = trim($sectionInnerCrawler->filter('h4.m-t-md.m-b-none')->first()->text());

                    if (!$catalog->image && $sectionInnerCrawler->filter('.thumb-info-wrapper.tf-none img')->first()->count() != 0) {
                        $src = $sectionInnerCrawler->filter('.thumb-info-wrapper.tf-none img')->first()->attr('src');
                        $uploadPath = Catalog::UPLOAD_URL;
                        $ext = $this->getExtensionFromSrc($src);
                        $fileName = $catalog->alias . $ext;
                        $res = $this->downloadJpgFile($src, $uploadPath, $fileName);
                        if ($res) {
                            $catalog->image = $fileName;
                            $catalog->save();
                        }
                    }

                    $this->parseCategoryTubes($name, $url, $catalog->id);
                });
            } else {
                //парсим товары
                try {
                    $this->parseListProductTubes($catalog, $categoryUrl);
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

    public function parseListProductTubes($catalog, $categoryUrl) {
        $this->info('Parse products from: ' . $catalog->name);
        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $crawler = new Crawler($html); //products page from url
            $uploadPath = $this->basePath . $catalog->alias . '/';

            //announce category
            if ($crawler->filter('.term-description')->count() != 0) {
                $catalog->announce = $crawler->filter('.term-description')->html();
                $catalog->save();
            }

            $crawler->filter('li.product-col')
//                    ->reduce(function (Crawler $none, $i) {return ($i < 1);})
                ->each(function (Crawler $node, $n) use ($catalog, $uploadPath) {
                    $data = [];
                    try {
                        $url = $this->baseUrl . $node->filter('a.product-loop-title')->first()->attr('href');
                        $data['name'] = trim($node->filter('h3.woocommerce-loop-product__title')->first()->text());

                        if ($node->filter('span.woocommerce-Price-amount.amount')->count() != 0) {
                            $rawPrice = $node->filter('span.woocommerce-Price-amount.amount')->first()->text();
                            $data['price'] = preg_replace("/[^,.0-9]/", null, $rawPrice);
                            $data['price'] = $this->replaceFloatValue($data['price']);
                            $data['in_stock'] = 1;
                        } else {
                            $data['price'] = null;
                            $data['in_stock'] = 0;
                        }

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
                            if ($productCrawler->filter('#tab-description')->first()->count() != 0) {
                                $data['text'] = $productCrawler->filter('#tab-description')->first()->html();
                            }

                            $newProd = Product::create(array_merge([
                                'catalog_id' => $catalog->id,
                                'parse_url' => $url,
                                'published' => 1,
                                'order' => $catalog->products()->max('order') + 1,
                            ], $data));

                            //характеристики
                            if ($productCrawler->filter('table.woocommerce-product-attributes')->count() != 0) {
                                $productCrawler->filter('.table.woocommerce-product-attributes tr')->each(function (Crawler $char) use ($newProd) {
                                    $name = $char->filter('.woocommerce-product-attributes-item__label')->first()->text();
                                    if ($char->filter('.woocommerce-product-attributes-item__value a')->count() != 0) {
                                        $value = trim($char->filter('.woocommerce-product-attributes-item__value a')->first()->text());
                                    } else {
                                        $value = trim($char->filter('.woocommerce-product-attributes-item__value')->first()->text());
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
                            if ($productCrawler->filter('.woocommerce-main-image.img-responsive')->count() != 0) {
                                $productCrawler->filter('.woocommerce-main-image.img-responsive')->each(function ($img, $n) use ($newProd, $catalog, $uploadPath) {
                                    //сохраняем 1 картинку
                                    if ($n == 0) {
                                        $imageSrc = $img->attr('src');
                                        //заглушка нет картинки
                                        if ($imageSrc != 'https://set-iset.ru/wp-content/uploads/woocommerce-placeholder-700x700.png' ||
                                            $imageSrc != 'https://set-iset.ru/wp-content/uploads/woocommerce-placeholder-500x500.png') {
                                            $ext = $this->getExtensionFromSrc($imageSrc);
                                            $fileName = 'tube_' . $newProd->id . $ext;
                                            $res = $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
                                            if ($res) {
                                                ProductImage::create([
                                                    'product_id' => $newProd->id,
                                                    'image' => $uploadPath . $fileName,
                                                    'order' => ProductImage::where('product_id', $newProd->id)->max('order') + 1,
                                                ]);
                                            }
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
                        $this->warn('error parse product: ' . $e->getMessage());
                        $this->warn('see line: ' . $e->getLine());
                        exit();
                    }
                });

            //проход по страницам
            if ($crawler->filter('.next.page-numbers')->count() != 0) {
                $nextUrl = $crawler->filter('.next.page-numbers')->attr('href');
                $this->info('parse next url: ' . $nextUrl);
                $this->parseListProductTubes($catalog, $nextUrl);
            }

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

    public function extractMeasureFromPrice(string $price): ?string {
        $f = strripos($price, '/');
        if (!$f) return null;

        return substr($price, $f + 1);
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
