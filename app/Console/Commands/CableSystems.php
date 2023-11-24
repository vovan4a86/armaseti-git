<?php

namespace App\Console\Commands;

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
use Symfony\Component\DomCrawler\Crawler;
use App\Traits\ParseFunctions;

class CableSystems extends Command {

    use ParseFunctions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:cs';
    private $basePath = ProductImage::UPLOAD_URL . 'cable-systems/';
    private $certificatesPath = 'uploads/certificates/';
    public $baseUrl = 'https://asd-e.ru';
    public $prefix = '';
    public $client;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсим кабельную продукцию';

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
//        $this->parseUrl('https://asd-e.ru/product/kabelenesushchie-sistemy/lotki-listovye-st/');
//        exit();

        foreach ($this->categoryList() as $categoryName => $categoryUrl) {
            $this->parseCategoryCableSystems($categoryName, $categoryUrl, 2);
        }
        $this->info('The command was successful!');
    }

    public function categoryList(): array {
        return [
//            'ГЭМ' => 'https://asd-e.ru/product/gem/',
            'СТАНДАРТ' => 'https://asd-e.ru/product/kabelenesushchie-sistemy/',
//            'PROMTRAY' => 'https://asd-e.ru/product/promtray/',
        ];
    }

    public function parseCategoryCableSystems($categoryName, $categoryUrl, $parentId) {
        $this->info($categoryName . ' => ' . $categoryUrl);
//        if ($categoryName == 'Метрический крепеж') $categoryName .= ' PT';
        $uploadPath = Catalog::UPLOAD_URL . 'inner_text_imgs/';

        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $sectionCrawler = new Crawler($html); //section page from url

            $title = $sectionCrawler->filter('#pagetitle')->first()->text();
            $catalog = $this->getCatalogByName($title, $parentId);

            //текст раздела
            if ($sectionCrawler->filter('.text_after_items')->count() != 0) {
                $text = $sectionCrawler->filter('.text_after_items')->html();

                if ($sectionCrawler->filter('.text_after_items img')->count() != 0) {
                    $imgSrc = [];
                    $newImgSrc = [];
                    $sectionCrawler->filter('.text_after_items img')
                        ->each(function (Crawler $img, $n) use (&$newImgSrc, &$imgSrc, $catalog, $uploadPath) {
                            $imageSrc = $this->baseUrl . $img->attr('src');
                            $find = $img->attr('src');
                            $ext = $this->getExtensionFromSrc($imageSrc);
                            $fileName = 'section_' . $catalog->id . '_text_img_' . $n . $ext;
                            if (!file_exists(public_path($uploadPath . $fileName))) {
                                $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
                            }
                            $imgSrc[] = $this->encodeUrlFileName($find);
                            $newImgSrc[] = $uploadPath . $fileName;
                        });
                    $catalog->text = $this->getUpdatedTextWithNewImages($text, $imgSrc, $newImgSrc);
                    $catalog->save();
                } else {
                    $catalog->text = $text;
                    $catalog->save();
                }
            }

            if ($sectionCrawler->filter('.item-views.catalog.sections .title a.dark-color')->count() != 0) {
                $sectionCrawler->filter('.item-views.catalog.sections .title a.dark-color')
                    ->each(function (Crawler $sectionInnerCrawler) use ($catalog) {
                        $name = trim($sectionInnerCrawler->text());
//
//                        if($catalog->name == 'Лотки листовые TS') $this->prefix = ' TS';
//                        elseif($catalog->name == 'Лотки лестничные TL') $this->prefix = ' TL';
//                        elseif($catalog->name == 'Лотки лестничные усиленные THL') $this->prefix = ' THL';
//                        elseif($catalog->name == 'Монтажные элементы MP') $this->prefix = ' MP';
//                        else $this->prefix = '';
//
                        $url = $this->baseUrl . $sectionInnerCrawler->attr('href');
                        $this->parseCategoryCableSystems($name, $url, $catalog->id);
                    });
            } else {
                //парсим товары
//                try {
//                    $this->parseListProductCableSystems($catalog, $categoryUrl);
//                } catch (\Exception $e) {
//                    $this->error('Error Parse Products from section: ' . $e->getMessage());
//                    $this->error('See line: ' . $e->getLine());
//                }
            }
        } catch (GuzzleException $e) {
            $this->error('Error Parse Sections: ' . $e->getMessage());
        }
    }

    public function parseListProductCableSystems($catalog, $categoryUrl) {
        $this->info('Parse products from: ' . $catalog->name);
        try {
            $res = $this->client->get($categoryUrl);
            $html = $res->getBody()->getContents();
            $crawler = new Crawler($html); //products page from url

            $uploadPath = $this->basePath . $catalog->alias . '/';

            if ($crawler->filter('.catalog.item-views.table.one')->count() != 0) {
                $table = $crawler->filter('.catalog.item-views.table.one')->first(); //table of products
                $table->filter('a.dark-color')
//                    ->reduce(function (Crawler $none, $i) {return ($i < 3);})
                    ->each(function (Crawler $node, $n) use ($catalog, $uploadPath) {
                        $data = [];
                        try {
                            $url = $this->baseUrl . trim($node->attr('href'));
                            $data['name'] = trim($node->text());
                            $data['h1'] = $node->text();
                            $data['title'] = $node->text();
                            $data['alias'] = Text::translit($node->text());

                            $this->info(++$n . ') ' . $data['name']);

                            $product = Product::whereParseUrl($url)->first();

                            if (!$product) {
                                $productPage = $this->client->get($url);
                                $productHtml = $productPage->getBody()->getContents();
                                $productCrawler = new Crawler($productHtml); //product page

                                $order = $catalog->products()->max('order') + 1;
                                $newProd = Product::create(array_merge([
                                    'catalog_id' => $catalog->id,
                                    'parse_url' => $url,
                                    'published' => 1,
                                    'order' => $order,
                                ], $data));

                                //текстовое описание товара
                                if ($productCrawler->filter('.content')->count() != 0) {
                                    $text = $productCrawler->filter('.content')->html();
                                    if ($productCrawler->filter('.content img')->count() != 0) {
                                        $imgSrc = [];
                                        $newImgSrc = [];
                                        $productCrawler->filter('.content img')
                                            ->each(function (Crawler $img) use ($newProd, $uploadPath, &$newImgSrc, &$imgSrc) {
                                                $imageSrc = $this->baseUrl . $img->attr('src');
                                                $find = $img->attr('src');
                                                $ext = $this->getExtensionFromSrc($imageSrc);
                                                $fileName = 'product_' . $newProd->id . '_text_img' . $ext;
                                                if (!file_exists(public_path($uploadPath . $fileName))) {
                                                    $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
                                                }

                                                $imgSrc[] = $this->encodeUrlFileName($find);
                                                $newImgSrc[] = $uploadPath . $fileName;
                                            });
                                        $newProd->text = $this->getUpdatedTextWithNewImages($text, $imgSrc, $newImgSrc);
                                        $newProd->save();
                                    } else {
                                        $newProd->text = $text;
                                        $newProd->save();
                                    }
                                }

                                //характеристики
//                                if ($productCrawler->filter('#props tr.char')->count() != 0) {
//                                    $chars = [];
//                                    $productCrawler->filter('#props tr.char')->each(function (Crawler $char) use (&$chars) {
//                                        $name = $char->filter('.char_name span')->text();
//                                        $value = $char->filter('.char_value span')->text();
//                                        $chars[$name] = $value;
//                                    });
//                                    $newProd->chars = $this->getTextFromCharArray($chars);
//                                    $newProd->save();
//                                }
                                if ($productCrawler->filter('#props tr.char')->count() != 0) {
                                    $productCrawler->filter('#props tr.char')->each(function (Crawler $char) use ($newProd) {
                                        $name = $char->filter('.char_name span')->text();
                                        $value = $char->filter('.char_value span')->text();

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

                                //сертификаты и ту
                                if ($productCrawler->filter('#docs a')->count() != 0) {
                                    $productCrawler->filter('#docs a')->each(function (Crawler $img, $n) use ($newProd, $uploadPath) {
                                        $url = $this->baseUrl . $img->attr('href');
                                        $ext = $this->getExtensionFromSrc($url);
                                        $fileName = 'product_' . $newProd->id . '_' . $n . $ext;
                                        $res = $this->downloadJpgFile($url, $this->certificatesPath, $fileName);
                                        if ($res) {
                                            ProductCertificate::create([
                                                'product_id' => $newProd->id,
                                                'image' => $fileName,
                                                'order' => ProductCertificate::where('product_id', $newProd->id)->max('order') + 1,
                                            ]);
                                        }
                                    });
                                }

                                //сохраняем изображения товара
                                $productCrawler->filter('.slides.items li img')->each(function ($img) use ($newProd, $catalog, $uploadPath) {
                                    $imageSrc = $this->baseUrl . $img->attr('src');
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
                                });
                            } else {
                                $product->update($data);
                                $product->save();
                            }
                        } catch (\Exception $e) {
                            $this->warn('error: ' . $e->getMessage());
                            $this->warn('see line: ' . $e->getLine());
                        }
                        sleep(rand(0, 2));
                    });
            }
            //проход по страницам
            if ($crawler->filter('.next a')->count() != 0) {
                $nextUrl = $crawler->filter('.next a');
                $nextUrl = $this->baseUrl . $nextUrl->attr('href');
                $this->info('parse next url: ' . $nextUrl);
                $this->parseListProductCableSystems($catalog, $nextUrl);
            }

        } catch (GuzzleException $e) {
            $this->error('Error Parse Product: ' . $e->getMessage());
            $this->error('See: ' . $e->getLine());
        }
    }

    public function getTextFromCharArray(array $chars): ?string {
        if (!count($chars)) return null;

        $res = '<ul class="prod-char">';
        foreach ($chars as $name => $value) {
            $res .= "<li><span class='char-name'>$name</span> - <span class='char-value'>$value</span></li>";
        }
        $res .= '</ul>';
        return $res;
    }

    public function parseUrl($url) {
        $productPage = $this->client->get($url);
        $productHtml = $productPage->getBody()->getContents();
        $productCrawler = new Crawler($productHtml); //product page


        if ($productCrawler->filter('.text_after_items')->count() != 0) {
            $text = $productCrawler->filter('.text_after_items')->html();
            $uploadPath = '/tmp-pic/';

            if ($productCrawler->filter('.text_after_items img')->count() != 0) {
                $imgSrc = [];
                $newImgSrc = [];
                $productCrawler->filter('.text_after_items img')
                    ->each(function (Crawler $img, $n) use (&$newImgSrc, &$imgSrc, $uploadPath) {
                        $imageSrc = $this->baseUrl . $img->attr('src');
                        $find = $img->attr('src');
                        $ext = $this->getExtensionFromSrc($imageSrc);
                        $fileName = 'section_' . '111' . '_text_img_' . $n . $ext;
//                        if (!file_exists(public_path($uploadPath . $fileName))) {
//                            $this->downloadJpgFile($imageSrc, $uploadPath, $fileName);
//                        }
                        $imgSrc[] = $this->encodeUrlFileName($find);
                        $newImgSrc[] = $uploadPath . $fileName;
                    });
                var_dump($imgSrc);
                var_dump($newImgSrc);
                $newtext = $this->getUpdatedTextWithNewImages($text, $imgSrc, $newImgSrc);
                $this->info($newtext);
            }
        }
    }
}
