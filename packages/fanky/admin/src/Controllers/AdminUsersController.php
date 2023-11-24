<?php namespace Fanky\Admin\Controllers;
use Request;
use Validator;
use DB;
use App\User;

class AdminUsersController extends AdminController {

	public function getIndex()
	{
		$users = User::all();

		return view('admin::users.main', ['users' => $users]);
	}

	public function postEdit($id = null)
	{
		if (!$id || !($user = User::findOrFail($id))) {
			$user = new User;
		}

		return view('admin::users.edit', ['user' => $user]);
	}

	public function postSave()
	{
		$id = Request::input('id');
		$data = Request::only(['name', 'email', 'role', 'username']);
		$password = Request::input('password');
		if ($password) $data['password'] = $password;

		// валидация данных
		$validator = Validator::make(
		    $data,
		    [
		    	'username' => 'required',
		    ]
		);
		if ($validator->fails()) {
			return ['errors' => $validator->messages()];
		}

		// сохраняем страницу
		$user = User::find($id);
		if (!$user) {
			$data['status'] = 1;
			$user = User::create($data);
		} else {
			$user->update($data);
		}

		return ['success' => true, 'id' => $user->id, 'row' => view('admin::users.user_row', ['item' => $user])->render()];
	}

	public function postDelete($id)
	{
		$user = User::findOrFail($id);
		$user->delete();

		return ['success' => true];
	}
}
