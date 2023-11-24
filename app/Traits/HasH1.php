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


trait HasH1{
	public function getH1() {
        return $this->h1 ?: $this->name;
	}
}