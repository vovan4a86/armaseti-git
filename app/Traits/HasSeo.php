<?php namespace App\Traits;
use Illuminate\Support\Str;
use Image;
use OpenGraph;
use SEOMeta;
use Settings;
use Thumb;

/**
 * Created by PhpStorm.
 * User: aleks
 * Date: 19.12.2017
 * Time: 11:09
 */


trait HasSeo{
	public function setSeo() {
        SEOMeta::setTitle($this->title);
        SEOMeta::setDescription($this->description);
        SEOMeta::setKeywords($this->keywords);
	}
}