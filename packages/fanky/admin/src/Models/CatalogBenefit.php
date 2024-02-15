<?php namespace Fanky\Admin\Models;

use App\Traits\HasImage;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thumb;

class CatalogBenefit extends Model {

	use HasImage;
	protected $table = 'catalog_benefits';

	protected $guarded = ['id'];

	public $timestamps = false;

	const UPLOAD_URL = '/uploads/catalogs/benefits/';

	public static $thumbs = [
		1 => '40x40', //item
	];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class)->withDefault();
    }

    public function getIsIconAttribute(): string {
        if ($this->image) {
            $arr = explode('.', $this->image);
            if ($arr[1] == 'svg') return true;
        }

        return false;
    }

}
