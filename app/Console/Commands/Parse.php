<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Fanky\Admin\Models\ParseItem;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DomCrawler\Crawler;

class Parse extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse';
    private $settings = null;
    private $baseUrl = 'https://shop.lonmadi.ru';

    public function __construct() {
        parent::__construct();
        $this->settings = (object)[
            'search_items' => '.sidebar-page-content .product-items a.product-item-title'
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $items = ParseItem::query()
            ->whereNull('result')
            ->whereNull('parse_date')
            ->limit(10000)
            ->get();
        $this->output->progressStart($items->count());
        foreach ($items as $item){
            $link = $this->searchItem($item);
            if($link){ //если нашли элемент
                $item->fill([
                    'result' => 'Найдено',
                    'url'	=> $link,
                    'filtered_url'	=> $link,
                    'parse_date' => Carbon::now()
                ])->save();
            } else { //если не найдено
                $item->fill([
                    'result' => 'Не найдено',
                    'parse_date' => Carbon::now()
                ])->save();
            }
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }

    public function searchItem(ParseItem $parseItem) {
        $url = 'https://shop.lonmadi.ru/catalog.html'; //?ProductsSearch%5Bsearchstring%5D=000362600
        $client = new Client();
        $res = $client->get($url, [
            'query' => [
                'ProductsSearch[searchstring]' => $parseItem->articul,
                'search-param' => 'article'
            ]
        ]);
        if($res->getStatusCode() == 200){
            $html = $res->getBody()->getContents();
            $crawler = new Crawler($html);
            if($crawler->filter($this->settings->search_items)->count() >= 1){
                $linkNode = $crawler->filter($this->settings->search_items)->first();
                if($linkNode){
                    return $link = $this->baseUrl . $linkNode->attr('href');
                }
            };

            return null;
            // Get images from page.
//			$images = $crawler->filter($parser->settings->image)->each(function (Crawler $node, $i) {
//				return $node->image()->getUri();
//			});
        }
    }
}
