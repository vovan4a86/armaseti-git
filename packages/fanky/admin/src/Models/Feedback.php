<?php namespace Fanky\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

/**
 * Fanky\Admin\Models\Feedback
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property array $data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $read_at
 * @property string|null $deleted_at
 * @property-read mixed $data_info
 * @property-read mixed $type_name
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback notRead()
 * @method static \Illuminate\Database\Query\Builder|\Fanky\Admin\Models\Feedback onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Fanky\Admin\Models\Feedback withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Fanky\Admin\Models\Feedback withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Feedback query()
 */
class Feedback extends Model {

	use SoftDeletes;

    protected $table = 'feedbacks';

    const UPLOAD_URL = '/uploads/feedbacks/';

    protected $guarded = ['id'];

	protected $casts = [
		'data' => 'array',
	];

	public static $types = array(
        1 => 'Заказ звонка',
        2 => 'Расчет цены',
        3 => 'Обратная связь',
        4 => 'Запрос прайс-листа',
    );

    private static $fields = [
    	'name' => 'Имя',
    	'phone' => 'Телефон',
    	'email' => 'E-mail',
    	'text' => 'Текст',
    	'message' => 'Сообщение',
    ];

    public function scopeNotRead($query)
	{
		return $query->whereNull('read_at');
	}

    public function getTypeNameAttribute()
    {
    	return array_get(self::$types, $this->type);
    }

    public function getDataInfoAttribute()
    {
    	$info = [];
    	foreach ($this->data as $key => $value) {
    		switch ($key) {
    			case 'products':
    				$value2 = [];
    				foreach ($value as $item) {
    					$value2[] = array_get($item, 'name').' '.array_get($item, 'size').' '.array_get($item, 'count').'шт.';
    				}
    				$value = $value2;
    			case 'product_id':
    				$product = Product::find($value);
    				if ($product) $value = '<span>'.$product->name.'</span>';
    				else $value = 'товара нет в каталоге';
    			default:
    				if (empty($value)) $value = '<i>не указано</i>';
    				$info[] = '<b>'.array_get(self::$fields, $key, $key).'</b>: '.(is_array($value) ? implode(', ', $value) : $value);
    		}
    	}
    	return implode('; ', $info);
    }

	public static function addItem($type, $data = array())
    {
        $arr = [];
        $arr['user_id'] = Auth::logedIn() ? Auth::user()->id : 0;
        $arr['type'] = $type;
        $arr['data'] = $data;
        return self::create($arr);
    }

}
