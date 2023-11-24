<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\CatalogParam;
use Fanky\Admin\Models\Param;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductImage;
use Fanky\Admin\Text;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;
use SiteHelper;
use Symfony\Component\DomCrawler\Crawler;
use SVG\SVG;
use App\Traits\ParseFunctions;

class CableProducts extends Command
{

    use ParseFunctions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:cp';
    private $basePath = ProductImage::UPLOAD_URL . 'cable-products/';
    private $baseUrl = 'https://rus-kab.ru';
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
    public function __construct()
    {
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
    public function handle()
    {
        foreach ($this->categoryList() as $categoryName => $categoryUrl) {
            $this->parseCategoryCableProducts($categoryName, $categoryUrl, 1);
        }
        $this->info('The command was successful!');
    }

    public function categoryList(): array
    {
        return [
            'Кабели силовые с ПВХ изоляцией' => 'https://rus-kab.ru/catalog/kabeli-silovye-s-pvkh-izolyatsiey/',
//            'Кабели гибкие с резиновой изоляцией' => 'https://rus-kab.ru/catalog/kabeli-gibkie-s-rezinovoy-izolyatsiey/',
//            'Авиационный провод' => 'https://rus-kab.ru/catalog/aviatsionnyy-provod/',
//            'Автомобильный провод' => 'https://rus-kab.ru/catalog/avtomobilnyy-provod/',
//            'Волоконно-оптический кабель' => 'https://rus-kab.ru/catalog/volokonno-opticheskiy-kabel/',
//            'Кабели бронированные' => 'https://rus-kab.ru/catalog/kabeli-bronirovannye/',
//            'Кабели и провода монтажные' => 'https://rus-kab.ru/catalog/kabeli-i-provoda-montazhnye/',
//            'Кабели контрольные' => 'https://rus-kab.ru/catalog/kabeli-kontrolnye/',
//            'Кабели связи' => 'https://rus-kab.ru/catalog/kabeli-svyazi/',
//            'Кабели сигнальные огнестойкие' => 'https://rus-kab.ru/catalog/kabeli-signalnye-ognestoykie/',
//            'Кабели силовые с бумажной изоляцией' => 'https://rus-kab.ru/catalog/kabeli-silovye-s-bumazhnoy-izolyatsiey/',
//            'Кабели управления' => 'https://rus-kab.ru/catalog/kabeli-upravleniya/',
//            'Провода для воздушных линий' => 'https://rus-kab.ru/catalog/provoda-dlya-vozdushnykh-liniy/',
//            'Провода изолированные самонесущие' => 'https://rus-kab.ru/catalog/provoda-izolirovannye-samonesushchie-sip/',
//            'Провода обмоточные' => 'https://rus-kab.ru/catalog/provoda-obmotochnye/',
//            'Провода установочные' => 'https://rus-kab.ru/catalog/provoda-ustanovochnye/',
//            'Радиочастотный кабель (коаксиальный)' => 'https://rus-kab.ru/catalog/radiochastotnyy-kabel-koaksialnyy/',
//            'Судовой кабель' => 'https://rus-kab.ru/catalog/sudovoy-kabel/',
        ];
    }

    public function parseCategoryCableProducts($categoryName, $categoryUrl, $parentId)
    {
        $this->info($categoryName . ' => ' . $categoryUrl);
        $catalog = $this->getCatalogByName($categoryName, $parentId);

        $html = $this->curlGetData($categoryUrl);
        $sectionCrawler = new Crawler($html); //section page from url

        if ($sectionCrawler->filter('ul.bx_catalog_tile_ul')->first()->count() != 0) {
            if ($sectionCrawler->filter('.descr img')->first()->count() != 0 && $catalog->image == null) {
                $uploadPath = Catalog::UPLOAD_URL;
                $imgUrl = $this->baseUrl . $sectionCrawler->filter('.descr img')->first()->attr('src');
                $ext = $this->getExtensionFromSrc($imgUrl);
                $fileName = $catalog->alias . '_section_img' . $ext;

                if (!file_exists(public_path($uploadPath . $fileName))) {
                    $hasImage = $this->downloadJpgFile($imgUrl, $uploadPath, $fileName);
                    if ($hasImage) {
                        $catalog->image = $fileName;
                    }
                }
            }
            $text = $sectionCrawler->filter('.descr p')->first()->text();
            $catalog->text = $text;
            $catalog->save();

            $sectionCrawler->filter('.bx_catalog_tile_ul li')
                ->each(function (Crawler $inner) use ($catalog) {
                    $tempName = $inner->filter('.bx_catalog_tile_title a')->first()->text();
                    $innerCatName = $this->extractCableProductName($tempName);
                    $innerCatUrl = $this->baseUrl . $inner->filter('.bx_catalog_tile_title a')->first()->attr('href');
                    $this->parseCategoryCableProducts($innerCatName, $innerCatUrl, $catalog->id);
                });
        } else {
            try {
                $this->parseCableProducts($catalog, $html);
            } catch (\Exception $e) {
                $this->warn('Error Parse From Section: ' . $e->getMessage());
            }
        }
    }

    public function parseCableProducts($catalog, $html)
    {
        $crawler = new Crawler($html); //page from url

        $h1 = $crawler->filter('h1.pageTitle')->first()->text();
        $description = null;
        if ($crawler->filter('.catalog-image')->first()->count() != 0 && $catalog->image == null) {
            $uploadPath = Catalog::UPLOAD_URL;
            $imgUrl = $this->baseUrl . $crawler->filter('.catalog-image')->first()->attr('href');
            $ext = $this->getExtensionFromSrc($imgUrl);
            $fileName = $catalog->alias . '_section_img' . $ext;

            if (!file_exists(public_path($uploadPath . $fileName))) {
                $hasImage = $this->downloadJpgFile($imgUrl, $uploadPath, $fileName);
                if ($hasImage) {
                    $catalog->image = $fileName;
                }
            }
        }

        $catalog->h1 = $h1;
        $catalog->description = $description;
        $catalog->save();

        if ($crawler->filter('#size table')->first()->count() != 0) {
            try {
                $text = $crawler->filter('#detail-text')->html();
                $chars = $crawler->filter('#char')->html();
                $sphere = $crawler->filter('#sphere')->html();
                $catalog->text = $text;
                $catalog->chars = $chars;
                $catalog->sphere = $sphere;
                $catalog->save();
            } catch (\Exception $e) {
                $this->warn('Parse SectionProducts error, CatalogName: ' . $catalog->name);
                $this->warn($e->getMessage());
            }

//            $table = $crawler->filter('#size table')->first();
//            $table->filter('tr')
//                ->each(function (Crawler $tr) use ($catalog) {
//                    $productName = $tr->filter('a')->first()->text();
//                    $parseUrl = $this->baseUrl . $tr->filter('a')->first()->attr('href');
//
//                    $product = Product::whereParseUrl($parseUrl)->first();
//
//                    if (!$product) {
//                        Product::create([
//                            'catalog_id' => $catalog->id,
//                            'name' => $productName,
//                            'alias' => Text::translit($productName),
//                            'order' => $catalog->products()->max('order') + 1,
//                            'parse_url' => $parseUrl
//                        ]);
//                    }
//                });
        }
    }

//        $uploadPath = '/data-site/';
//        $url = 'https://rus-kab.ru/catalog/kabeli-silovye-s-pvkh-izolyatsiey/avvg/';
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: beget=begetok"));
//        $response = curl_exec($ch);
//        curl_close($ch);

//        if (!is_dir(public_path($uploadPath))) {
//            mkdir(public_path($uploadPath), 0777, true);
//        }
//        $res = file_get_contents(public_path($uploadPath . '123.txt'));

//        $crawler = new Crawler($res); //page from url

//        $h2 = $crawler->filter('.r_cart_block h2')->first()->text();
//        $ul = $crawler->filter('.r_cart_block h2')->html();
//        $description = $crawler->filter('#detail-text')->html();
//        $char = $crawler->filter('#char')->html();
//        $sphere = $crawler->filter('#sphere')->html();

//        $table = $crawler->filter('#size table')->first();
//        $table->filter('tr')->each(function (Crawler $tr) {
//            $this->info($tr->filter('td')->first()->text());
//        });

//        $name = 'ВВГнг-FRLS (ВВГ нг FRLS)';
//        $name = 'АВВГ';
//        $this->info($this->extractCableProductName($name));

//        var_dump($sphere);


}
