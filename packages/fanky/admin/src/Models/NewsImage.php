<?php namespace Fanky\Admin\Models;

use App\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsImage extends Model {

	use HasImage;
	protected $table = 'news_images';

	protected $fillable = ['news_id', 'image', 'order'];

	public $timestamps = false;

	const UPLOAD_URL = '/uploads/news/gallery';

	public static $thumbs = [
		1 => '100x100|fit', //admin product
		2 => '116x186|fit', //article gallery
	];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }
}
