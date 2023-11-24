<?php namespace Fanky\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fanky\Admin\Models\SxgeoRegion
 *
 * @property int $id
 * @property string $iso
 * @property string $country
 * @property string $name_ru
 * @property string $name_en
 * @property string $timezone
 * @property string $okato
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion whereIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion whereNameRu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion whereOkato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoRegion whereTimezone($value)
 * @mixin \Eloquent
 */
class SxgeoRegion extends Model {

	protected $table = 'sxgeo_regions';
}
