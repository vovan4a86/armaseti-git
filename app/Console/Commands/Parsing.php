<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler;

class Parsing extends Command
{
    public $use_proxy = false;
    public $convert_to_utf = true;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parsing';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private $proxy = [
//		'109.202.10.45:3129',
        '212.41.46.17:8080',
        '176.62.191.24:80',
//		'217.114.153.63:80',
        '87.255.4.136:3128',
        '91.210.192.194:8080',
        '145.255.4.150:8080',
        '81.91.42.174:8080',
        '84.42.3.3:3128',
//		'109.172.63.34:3128',
//		'188.134.76.66:3129',
        '188.134.76.66:80',
        '213.85.92.10:80',
        '85.26.146.170:80',
        '37.18.152.132:80',
        '87.255.4.136:3128',
//		'188.226.201.242:3128',
    ];
    private $user_agent_list = [
        'Mozilla/5.0 (Linux; Android 6.0.1; SM-G920V Build/MMB29K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.98 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 5.1.1; SM-G928X Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.83 Mobile Safari/537.36',
        'Mozilla/5.0 (Windows Phone 10.0; Android 4.2.1; Microsoft; Lumia 950) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Mobile Safari/537.36 Edge/13.10586',
        'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 6P Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.83 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 6.0.1; E6653 Build/32.2.A.0.253) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.98 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 6.0; HTC One M9 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.98 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 7.0; Pixel C Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/52.0.2743.98 Safari/537.36',
        'Mozilla/5.0 (Linux; Android 6.0.1; SGP771 Build/32.2.A.0.253; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/52.0.2743.98 Safari/537.36',
        'Mozilla/5.0 (Linux; Android 5.1.1; SHIELD Tablet Build/LMY48C) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.98 Safari/537.36',
        'Mozilla/5.0 (Linux; Android 5.0.2; SAMSUNG SM-T550 Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/3.3 Chrome/38.0.2125.102 Safari/537.36',
        'Mozilla/5.0 (Linux; Android 4.4.3; KFTHWI Build/KTU84M) AppleWebKit/537.36 (KHTML, like Gecko) Silk/47.1.79 like Chrome/47.0.2526.80 Safari/537.36',
        'Mozilla/5.0 (Linux; Android 5.0.2; LG-V410/V41020c Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/34.0.1847.118 Safari/537.36',
        'Mozilla/5.0 (CrKey armv7l 1.5.16041) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.0 Safari/537.36',
        'Mozilla/5.0 (Nintendo WiiU) AppleWebKit/536.30 (KHTML, like Gecko) NX/3.0.4.2.12 NintendoBrowser/4.3.1.11264.US',
        'Mozilla/5.0 (Windows Phone 10.0; Android 4.2.1; Xbox; Xbox One) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Mobile Safari/537.36 Edge/13.10586',
        'Mozilla/5.0 (PlayStation 4 3.11) AppleWebKit/537.73 (KHTML, like Gecko)',
        'Mozilla/5.0 (PlayStation Vita 3.61) AppleWebKit/537.73 (KHTML, like Gecko) Silk/3.2',
        'Mozilla/5.0 (Nintendo 3DS; U; ; en) Version/1.7412.EU',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
        'Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
        'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
        'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('use parsing:par_name');
    }

    /**
     * Загрузка страницы
     *
     * @param string $url
     * @param string|null $my_proxy
     * @param int|null $attempts
     * @return null|string
     * @throws Exception
     */
    protected function load_html($url, $my_proxy = null, $attempts = 0)
    {
        if (!$my_proxy) {
            $proxy_rnd_key = array_rand($this->proxy, 1);
            $proxy = $this->proxy[$proxy_rnd_key];
        } else $proxy = $my_proxy;
        $user_agent_rnd_key = array_rand($this->user_agent_list, 1);
        $user_agent = $this->user_agent_list[$user_agent_rnd_key];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language:ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3',
            'Connection:keep-alive',
            'Accept-Charset: UTF-8',
            'Cookie:city=66'
        ]);

        if ($this->use_proxy) {
            curl_setopt($ch, CURLOPT_PROXY, "$proxy");
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $ss = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
//		$this->info('http_code: ' . $info['http_code']);
//		$this->info('ss: ' . $ss);
        if ($info['http_code'] == 200 && !empty($ss)) {
//			$ss = mb_convert_encoding($ss, 'HTML-ENTITIES');
            if($this->convert_to_utf){
                $ss = mb_convert_encoding($ss, 'UTF-8', 'CP1251');
            }
//			dd($ss);

            return $ss;
//		} elseif ($info['http_code'] == 503) {
//			sleep(60);
        } elseif ($attempts++ <= 5) {
            return $this->load_html($url, $my_proxy, $attempts);
        } else {
            throw new Exception('Fail to load: ' . $url, 404);
        }
    }
}
