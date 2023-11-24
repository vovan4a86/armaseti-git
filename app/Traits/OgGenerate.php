<?php namespace App\Traits;
use Illuminate\Support\Str;
use Image;
use OpenGraph;
use Settings;
use Thumb;

/**
 * Created by PhpStorm.
 * User: aleks
 * Date: 19.12.2017
 * Time: 11:09
 */


trait OgGenerate{
	public function ogGenerate() {

		OpenGraph::setUrl($this->url);
		if($this->og_title || $this->title){
			OpenGraph::setTitle($this->og_title ?: $this->title);
		}
		if($this->og_description || $this->description){
			OpenGraph::setDescription($this->og_description ?: $this->description);
		}
		if($this->image_src){
			OpenGraph::addImage($this->image ? $this->image_src : '/apple-touch-icon.png');
		}
	}
}
