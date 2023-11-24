<?php namespace Fanky\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fanky\Admin\Models\SxgeoCountry
 *
 * @property int $id
 * @property string $iso
 * @property string $continent
 * @property string $name_ru
 * @property string $name_en
 * @property float $lat
 * @property float $lon
 * @property string $timezone
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereContinent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereNameRu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCountry whereTimezone($value)
 * @mixin \Eloquent
 */
class SxgeoCountry extends Model {

	protected $table = 'sxgeo_country';
}
