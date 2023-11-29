<?php
namespace Fanky\Admin\Models;

use App\Classes\SiteHelper;
use App\Traits\HasFile;
use App\Traits\HasH1;
use App\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;

/**
 * Fanky\Admin\Models\Review
 *
 * @property int $id
 * @property string $type
 * @property string|null $text
 * @property string $adress
 * @property string $video
 * @property int $on_main
 * @property int $order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $type_name
 * @property-read mixed $video_src
 * @property-read mixed $video_thumb
 * @property-read mixed $video_url
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review onMain()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereAdress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereOnMain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review whereVideo($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Review query()
 */
class Customer extends Model
{
    protected $guarded = ['id'];

    public function dateFormat($format = 'd F Y')
    {
        if (!$this->date) return null;
        $date = date($format, strtotime($this->date));
        $date = str_replace(array_keys(SiteHelper::$monthRu),
            SiteHelper::$monthRu, $date);

        return $date;
    }
}
