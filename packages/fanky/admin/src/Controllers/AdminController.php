<?php namespace Fanky\Admin\Controllers;

use App\Http\Controllers\Controller;
use Fanky\Admin\Models\AdminLog;
use Request;
use Validator;
use App\User;
use Auth;

use Fanky\Admin\Models\Participant;
use Fanky\Admin\Models\GalleryItem;
use Fanky\Admin\Models\Complex;
use Fanky\Admin\Models\Sponsor;
use Fanky\Admin\Models\Specialist;
use Image;
use Thumb;

class AdminController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware('auth.fanky');
		$this->middleware('menu.admin');
	}

	public function main() {
		$logs = AdminLog::orderBy('created_at', 'desc')->paginate(30);
		return view('admin::main', [
			'logs'	=> $logs
		]);
	}

}
