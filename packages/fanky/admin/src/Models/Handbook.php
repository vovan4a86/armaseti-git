<?php namespace Fanky\Admin\Models;

use App\Classes\SiteHelper;
use App\Traits\HasH1;
use App\Traits\HasImage;
use App\Traits\HasSeo;
use App\Traits\OgGenerate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Fanky\Admin\Models\Handbook
 *
 * @property int                 $id
 * @property int                 $published
 * @property string              $name
 * @property string|null         $text
 * @property string              $image
 * @property string              $alias
 * @property string              $title
 * @property string              $keywords
 * @property int                 $order
 * @property string              $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed          $url
 * @method static Builder|Handbook public ()
 * @method static Builder|Handbook whereAlias($value)
 * @method static Builder|Handbook whereCreatedAt($value)
 * @method static Builder|Handbook whereDescription($value)
 * @method static Builder|Handbook whereId($value)
 * @method static Builder|Handbook whereImage($value)
 * @method static Builder|Handbook whereKeywords($value)
 * @method static Builder|Handbook whereName($value)
 * @method static Builder|Handbook wherePublished($value)
 * @method static Builder|Handbook whereText($value)
 * @method static Builder|Handbook whereTitle($value)
 * @method static Builder|Handbook whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $h1
 * @property string|null $og_title
 * @property string|null $og_description
 * @method static Builder|Handbook newModelQuery()
 * @method static Builder|Handbook newQuery()
 * @method static Builder|Handbook query()
 * @method static Builder|Handbook whereH1($value)
 */
class Handbook extends Model {

	use HasImage, HasH1, HasSeo, OgGenerate;

	protected $table = 'handbook';

	protected $guarded = ['id'];

	const UPLOAD_URL = '/uploads/handbook/';

	public static $thumbs = [
		1 => '100x100', //admin
		2 => '470x262', //list
	];

	public function scopePublic($query) {
		return $query->where('published', 1);
	}

	public function getUrlAttribute($value): string
    {
		return route('handbook.item', ['alias' => $this->alias]);
	}

	public function getAnnounce(): ?string
    {
        if($this->text) {
            $city = SiteHelper::getCurrentCity();
            $search = ['{city}', '{city_name}'];
            if ($city) {
                $replace = [' в ' . $city->in_city, $city->name];
                $this->text = SiteHelper::replaceLinkToRegion($this->text, $city);
            } else {
                $replace = [' в Екатеринбурге', 'Екатеринбург'];
            }
            $this->text = str_replace($search, $replace, $this->text);
            return mb_strimwidth(strip_tags($this->text), 0, 300, '...');
        }
        return null;
    }
}
