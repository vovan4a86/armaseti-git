<?php namespace App\Http\Controllers;

use App;
use Fanky\Admin\Models\City;
use Request;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use SiteHelper;

abstract class Controller extends BaseController {
    /** @var ?City */
    public $city = null;
    use DispatchesJobs, ValidatesRequests;

    public function add_region_seo($page) {
        $this->city = SiteHelper::getCurrentCity();
        if (!isRegional($page)) return $page;
        $search = ['{city}', '{city_name}'];
        if ($this->city) {
            $replace = [' в ' . $this->city->in_city, $this->city->name];
            $in_city = ' в ' . $this->city->in_city;
            $page->text = SiteHelper::replaceLinkToRegion($page->text, $this->city);
        } else {
            $replace = [' в Екатеринбурге', 'Екатеринбург'];
            $in_city = '';
        }
        if (strpos($page->title, '{city}') === false && strpos($page->title, '{city_name}') === false) {
            $page->title .= '{city}';
        }
        if (strpos($page->description, '{city}') === false && strpos($page->description, '{city_name}') === false) {
            $page->description .= '{city}';
        }
        if ($page->keywords && strpos($page->keywords, '{city}') === false && strpos($page->keywords, '{city_name}') === false) {
            $page->keywords .= '{city}';
        }
        $page->text = str_replace($search, $replace, $page->text);
        $page->title = str_replace($search, $replace, $page->title);
        $page->description = str_replace($search, $replace, $page->description);
        $page->keywords = str_replace($search, $replace, $page->keywords);
        $page->text_prev = str_replace($search, $replace, $page->text_prev);
        $page->text_after = str_replace($search, $replace, $page->text_after);
        $page->features = str_replace($search, $replace, $page->features);
        $page->announce_text = str_replace($search, $replace, $page->announce_text);
        $page->seo_text = str_replace($search, $replace, $page->seo_text);

        $page->name .= $in_city;
        if ($page->h1) {
            $page->h1 .= $in_city;
        }

        return $page;
    }

    /**
     * @param Carbon $lastModifed
     * @return NULL|\Illuminate\Contracts\Routing\ResponseFactory|null|\Symfony\Component\HttpFoundation\Response
     */
    public function checkLastmodify($lastModifed) {
        if (Request::hasHeader('If-Modified-Since')) {
            $timestamp = Request::header('If-Modified-Since');
            $timestamp = Carbon::createFromFormat("D, d M Y H:i:s T", $timestamp);
            if ($lastModifed->between($timestamp->copy()->subMinute(1),
                $timestamp->copy()->addMinute(1))) return response('', 304);
        }

        return null;
    }
}
