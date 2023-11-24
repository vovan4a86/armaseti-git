<?php namespace Fanky\Admin\Controllers;

use Request;
use Validator;
use Pagination;
use DB;
use Fanky\Admin\Models\Feedback;

class AdminFeedbacksController extends AdminController {

	public function getIndex() {
		$feedbacks = Pagination::init(new Feedback, 20)->orderBy('created_at', 'desc')->get();

		return view('admin::feedback.main', ['feedbacks' => $feedbacks]);
	}

	public function postRead($id = null)
	{
		if ($id) {
			DB::table('feedbacks')->where('id', $id)->whereNull('read_at')->update(['read_at' => DB::raw('NOW()')]);
		} else {
			$ids = Request::input('id', []);
			if (!empty($ids)) DB::table('feedbacks')->whereIn('id', $ids)->whereNull('read_at')->update(['read_at' => DB::raw('NOW()')]);
		}

		return ['success' => true];
	}

	public function postDelete($id = null)
	{
		if ($id) {
			Feedback::destroy($id);
		} else {
			$ids = Request::input('id', []);
			if (!empty($ids)) Feedback::destroy($ids);
		}

		return ['success' => true];
	}
}
