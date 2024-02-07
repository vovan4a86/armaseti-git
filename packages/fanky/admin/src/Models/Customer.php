<?php
namespace Fanky\Admin\Models;

use App\Classes\SiteHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Fanky\Admin\Models\Customer
 *
 * @property int $id
 * @property string|null $text
 * @property string $details
 * @property int $on_main
 * @property int $order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static Builder|Customer whereCreatedAt($value)
 * @method static Builder|Customer whereId($value)
 * @method static Builder|Customer whereOnMain($value)
 * @method static Builder|Customer whereOrder($value)
 * @method static Builder|Customer whereText($value)
 * @method static Builder|Customer whereType($value)
 * @method static Builder|Customer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static Builder|Customer newModelQuery()
 * @method static Builder|Customer newQuery()
 * @method static Builder|Customer query()
 * @method static whereEmail(mixed $email)
 */
class Customer extends Model
{
    protected $guarded = ['id'];

    const UPLOAD_URL = '/uploads/customers/';

    public function dateFormat($format = 'd F Y')
    {
        if (!$this->date) return null;
        $date = date($format, strtotime($this->date));
        $date = str_replace(array_keys(SiteHelper::$monthRu),
            SiteHelper::$monthRu, $date);

        return $date;
    }

    public static function uploadDetails(UploadedFile $file): string {
        $file_name = md5(uniqid(rand(), true)) . '_' . time() . '.' . Str::lower($file->getClientOriginalExtension());
        $file->move(public_path(self::UPLOAD_URL), $file_name);
        return $file_name;
    }

    public function getFileSrcAttribute(): string
    {
        return self::UPLOAD_URL . $this->details;
    }
}
