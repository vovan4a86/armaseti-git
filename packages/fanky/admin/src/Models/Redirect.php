<?php namespace Fanky\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Redirect
 *
 * @property string		code_name
 * @property string		from
 * @property string		to
 * @property integer	code
 * @property integer	id
 * @package Fanky\Admin\Models
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Redirect whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Redirect whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Redirect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Redirect whereTo($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Redirect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Redirect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\Redirect query()
 */
class Redirect extends Model {

	public $timestamps = false;

	protected $fillable = ['from', 'to', 'code'];

	public static $codes = [
		301 => '301 Moved Permanently',
		302 => '302 Found',
	];

	public function getCodeNameAttribute(){
		return self::$codes[$this->attributes['code']];
	}
}
