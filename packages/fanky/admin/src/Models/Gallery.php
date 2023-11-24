<?php namespace Fanky\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fanky\Admin\Models\Gallery
 *
 * @property int $id
 * @property int $page_id
 * @property string $code
 * @property string $name
 * @property array $params
 * @property int $order
 * @property-read \Illuminate\Database\Eloquent\Collection|\Fanky\Admin\Models\GalleryItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery whereParams($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Gallery query()
 */
class Gallery extends Model {

	protected $table = 'galleries';

	protected $fillable = ['page_id', 'name', 'params', 'order'];

	protected $casts = [
		'params' => 'array',
	];

	public $timestamps = false;

	public function items()
	{
		return $this->hasMany('Fanky\Admin\Models\GalleryItem');
	}

	public function delete() {
		foreach ($this->items as $item){
			$item->delete();
		}

		parent::delete();
	}
}
