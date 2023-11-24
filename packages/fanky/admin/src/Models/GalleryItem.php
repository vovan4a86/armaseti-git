<?php namespace Fanky\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Thumb;

/**
 * Fanky\Admin\Models\GalleryItem
 *
 * @property int                              $id
 * @property int                              $gallery_id
 * @property string                           $image
 * @property array                            $data
 * @property int                              $order
 * @property-read \Fanky\Admin\Models\Gallery $gallery
 * @property-read mixed                       $src
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem whereGalleryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem whereOrder($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\GalleryItem query()
 */
class GalleryItem extends Model {

	protected $table = 'galleries_items';

	protected $fillable = ['gallery_id', 'image', 'data', 'order'];

	protected $casts = [
		'data' => 'array',
	];

	public $timestamps = false;

	const UPLOAD_PATH = '/public/uploads/gallery/';
	const UPLOAD_URL = '/uploads/gallery/';

	public static $thumbs = [
		1 => '228x213|fit',
	];

	public function gallery() {
		return $this->belongsTo('Fanky\Admin\Models\Gallery');
	}

	public function getSrcAttribute($value) {
		return $this->image ? url(self::UPLOAD_URL . $this->image) : null;
	}

	public function thumb($thumb) {
		if (!$this->image) {
			return null;
		} else {
			$file = public_path(self::UPLOAD_URL . $this->image);
			$file = str_replace(['\\\\', '//'], DIRECTORY_SEPARATOR, $file);
			$file = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $file);

			if (!is_file(public_path(Thumb::url(self::UPLOAD_URL . $this->image, $thumb)))) {
				if (!is_file($file)) return null; //нет исходного файла
				//создание миниатюры
				if (is_array($this->gallery->params) && !empty($this->gallery->params['thumbs'])) {
					$thumbs = $this->gallery->params['thumbs'];
				} else {
					$thumbs = self::$thumbs;
				}
				Thumb::make(self::UPLOAD_URL . $this->image, $thumbs);

			}

			return url(Thumb::url(self::UPLOAD_URL . $this->image, $thumb));
		}
	}
}
