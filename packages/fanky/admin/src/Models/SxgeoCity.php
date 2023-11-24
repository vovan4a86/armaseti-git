<?php namespace Fanky\Admin\Models;

use App\SxGeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Request;

/**
 * Fanky\Admin\Models\SxgeoCity
 *
 * @property int $id
 * @property int $region_id
 * @property string $name_ru
 * @property string $name_en
 * @property float $lat
 * @property float $lon
 * @property string $okato
 * @property string|null $zip
 * @property int|null $cost
 * @property int|null $cost_update
 * @property-read \Illuminate\Database\Eloquent\Collection|\Fanky\Admin\Models\City[] $cities
 * @property-read \Fanky\Admin\Models\SxgeoRegion $region
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereCostUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereNameRu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereOkato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\SxgeoCity whereZip($value)
 * @mixin \Eloquent
 */
class SxgeoCity extends Model {

	protected $table = 'sxgeo_cities';
	protected static $_cur_region;
	protected static $_default_region = 1486209;

	public function cities(): BelongsToMany
    {
		return $this->belongsToMany('Fanky\Admin\Models\City');
	}

	public function region(): BelongsTo
    {
		return $this->belongsTo('Fanky\Admin\Models\SxgeoRegion', 'region_id');
	}

	/**
	 * @return City|null
	 */
	public static function current(): ?City
    {
		$city_alias = session('city_alias', '');
		$city = null;
		if ($city_alias) {
			$city = City::whereAlias($city_alias)->first();
		} else {
			$ip = Request::getClientIp();
			$geo = new SxGeo(base_path('resources/SxGeoCity.dat'));
			$sxgeo_city = $geo->getCity($ip);
			if (isset($sxgeo_city['city']) && isset($sxgeo_city['city']['id'])) {
				$sxgeo_city_id = $sxgeo_city['city']['id'];
				$sxgeo_city = self::find($sxgeo_city_id);
				if ($sxgeo_city) {
					$city = $sxgeo_city->cities()->first();
				}
			}
		}

		return $city;
	}

	/**
	 * @return City|null
	 */
	public static function detect(): ?City
    {
		$city = null;
		$ip = env('MY_IP') ?: Request::getClientIp();
		$geo = new SxGeo(base_path('resources/SxGeoCity.dat'));
		$sxgeo_city = $geo->getCity($ip);
		if (isset($sxgeo_city['city']) && isset($sxgeo_city['city']['id'])) {
//			$sxgeo_city_id = $sxgeo_city['city']['id'];
			$sxgeo_city = $sxgeo_city['city']['name_ru'];
			if ($sxgeo_city) {
				$city = City::whereName($sxgeo_city)->first();
			}
		}

		return $city;
	}
}
