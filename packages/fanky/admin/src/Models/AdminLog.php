<?php namespace Fanky\Admin\Models;

use Carbon\Carbon;
use Fanky\Auth\Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * Fanky\Admin\Models\AdminLog
 *
 * @property int $id
 * @property string $user
 * @property string $msg
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog whereMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog whereUser($value)
 * @mixin \Eloquent
 * @property string $ip
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Fanky\Admin\Models\AdminLog query()
 */
class AdminLog extends Model {

	protected $guarded = ['id'];

	/* сколько дней хранить */
	public static $store_days = 60;

	public static function add($msg) {
		$user = Auth::user();

		$name = ($user) ? $user->username : 'console';
		$ip = \Request::ip();
		$data = [
			'user' => $name,
			'ip'   => $ip,
			'msg'  => $msg
		];

		self::create($data);
		self::where('created_at', '<', Carbon::now()->subDay(self::$store_days))->delete();
	}

	public static function last($count = 10) {
		$last = self::orderBy('created_at', 'desc')->limit($count)->get();

		return $last;
	}
}
