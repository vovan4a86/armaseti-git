<?php namespace Fanky\Admin\Controllers;

use DB;
use Request;
use Settings;
use Validator;
use Thumb;
use Image;
use Fanky\Admin\Models\Gallery;
use Fanky\Admin\Models\GalleryItem;

class AdminGalleryController extends AdminController {

	public function anyIndex()
	{
//		$g = Gallery::find(1); //Слайдер на главной
//		$g->params = ['thumbs' => [
//			1 => '315x156', //admin
//			2 => '1920x561' //slider
//		], 'fields' => [
//			'title' => ['type' => 0, 'title' => 'Заголовок'],
//			'text' => ['type' => 1, 'title' => 'Текст'],
//			'link' => ['type' => 0, 'title' => 'Ссылка'],
//		]];
//		$g->save();
		
		$galleries = Gallery::where('page_id', 0)->orderBy('order')->get();

		return view('admin::gallery.main', ['galleries' => $galleries]);
	}

	public function postGallerySave()
	{
		$id = Request::input('id');
		$data = Request::only(['name']);

		// сохраняем галерею
		$gallery = Gallery::find($id);
		if (!$gallery) {
			$gallery = Gallery::create($data);
		} else {
			$gallery->update($data);
		}

		return ['success' => true, 'view' => view('admin::gallery.gallery_row', ['gallery' => $gallery])->render()];
	}

	public function postGalleryDelete($id)
	{
		$gallery = Gallery::find($id);
		foreach ($gallery->items as $item) {
			@unlink(base_path() . $item::UPLOAD_PATH . $item->image);
			foreach (GalleryItem::$thumbs as $key => $value) {
				@unlink(base_path() . '/public' . Thumb::url(GalleryItem::UPLOAD_URL . $item->image, $key));
			}
			$item->delete();
		}
		$gallery->delete();

		return ['success' => true];
	}

	public function anyItems($id)
	{
		$gallery = Gallery::find($id);
		$items = $gallery->items()->orderBy('order')->get();
		if (!$items) $items = [];

		return view('admin::gallery.gallery_items', ['gallery' => $gallery, 'items' => $items]);
	}

	public function postImageUpload($gallery_id)
	{
		$gallery = Gallery::findOrFail($gallery_id);
		$images = Request::file('images');
		$items = [];
		if ($images) foreach ($images as $image) {
			$file_name = md5(uniqid(rand(), true)) . '.' . $image->getClientOriginalExtension();
			$image->move(base_path() . GalleryItem::UPLOAD_PATH, $file_name);
			Image::make(base_path() . GalleryItem::UPLOAD_PATH . $file_name)
				->resize(1920, 1080, function ($constraint) {
				    $constraint->aspectRatio();
				    $constraint->upsize();
				})
				->save(null, Settings::get('image_quality', 100));
			$item = GalleryItem::create(['gallery_id' => $gallery_id, 'image' => $file_name]);
			if (is_array($gallery->params) && !empty($gallery->params['thumbs'])) {
				Thumb::make(GalleryItem::UPLOAD_URL . $file_name, $gallery->params['thumbs']);
			} else {
				Thumb::make(GalleryItem::UPLOAD_URL . $file_name, GalleryItem::$thumbs);
			}
			$items[] = $item;
		}

		$html = '';
		foreach ($items as $item) {
			$html .= view('admin::gallery.item', ['item' => $item, 'gallery' => $gallery]);
		}

		return ['html' => $html];
	}

	public function postImageEdit($id)
	{
		$image = GalleryItem::findOrFail($id);
		return view('admin::gallery.item_data_edit', ['image' => $image]);
	}

	public function postImageDataSave($id)
	{
		$image = GalleryItem::findOrFail($id);
		$data = Request::only(array_keys($image->gallery->params['fields']));
		$image->data = $data;
		$image->save();
		return ['success' => true];
	}

	public function postImageDelete($id)
	{
		$item = GalleryItem::findOrFail($id);
		@unlink(base_path() . $item::UPLOAD_PATH . $item->image);
		foreach (GalleryItem::$thumbs as $key => $value) {
			@unlink(base_path() . '/public' . Thumb::url(GalleryItem::UPLOAD_URL . $item->image, $key));
		}
		$item->delete();

		return ['success' => true];
	}

	public function postImageOrder() {
		$sorted = Request::get('sorted', []);
		foreach ($sorted as $key => $item){
			GalleryItem::whereId($item)->update(['order' => $key]);
		}
		return response(['success' => true]);
	}
}
