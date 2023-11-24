<?php namespace Fanky\Admin\Controllers;

use Fanky\Admin\Models\Redirect;
use Request;
use Validator;
use DB;

class AdminRedirectsController extends AdminController {

	public function getIndex() {
		$items = Redirect::all();

		return view('admin::redirects.main', ['items' => $items]);
	}

	public function getEdit($id = null) {
		$redirect = Redirect::findOrNew($id);

		return view('admin::redirects.edit', ['item' => $redirect]);
	}

	public function postDelete($id) {
		$redirect = Redirect::find($id);
		if ($redirect) $redirect->delete();

		return ['success' => true];
	}

	public function postSave($id = null) {
		$redirect = Redirect::findOrNew($id);
		$data = Request::only(['from', 'to', 'code']);
		if (substr($data['from'], 0, 1) != '/') $data['from'] = '/' . $data['from'];
		if (substr($data['to'], 0, 1) != '/') $data['to'] = '/' . $data['to'];
		if ($redirect->id) {
			$redirect->update($data);
		} else {
			$redirect->create($data);
		}

		return redirect()->route('admin.redirects');
	}
}
