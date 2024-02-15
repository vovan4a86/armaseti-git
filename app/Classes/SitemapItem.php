<?php

namespace App\Classes;

class SitemapItem {
    /** @var string */
    public $loc;
    /** @var string */
    public $lastmod;
    /** @var string */
    public $priority;
    /** @var string */
    public $freq;
    public $images = [];
    /** @var string */
    public $title;
    public $translations = [];
    public $videos = [];
    public $googlenews = [];
    public $alternates = [];
    /** @var bool */
    public $isRegional;

    public function __construct($loc, $isRegional = null) {
        $this->loc = $loc;
        $this->isRegional = !!$isRegional;
    }

    public function addImage($url, $title = null, $caption = null, $geo_location = null, $license = null){
        $this->images[] = array_filter(
            compact('url', 'title', 'caption', 'geo_location', 'license')
        );
    }

    public function toArray(): array
    {
        return [
            'loc'           => $this->loc,
            'lastmod'       => $this->lastmod,
            'priority'      => $this->priority,
            'freq'          => $this->freq,
            'images'        => $this->images,
            'title'         => $this->title,
            'translations'  => $this->translations,
            'videos'        => $this->videos,
            'googlenews'    => $this->googlenews,
            'alternates'    => $this->alternates,
        ];
    }
}
