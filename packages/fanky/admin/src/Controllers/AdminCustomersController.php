<?php namespace Fanky\Admin\Controllers;
use Fanky\Admin\Models\Customer;
use Request;
use Validator;
use DB;
use App\User;

class AdminCustomersController extends AdminController {

	public function getIndex()
	{
		$customers = Customer::all();

		return view('admin::customers.main', ['customers' => $customers]);
	}

	public function postEdit($id = null)
	{
		if (!$id || !($customer = Customer::findOrFail($id))) {
            $customer = new Customer;
		}

		return view('admin::customers.edit', ['customer' => $customer]);
	}

	public function postSave(): array
    {
		$id = Request::input('id');
		$data = Request::only(['name', 'email', 'phone', 'username']);

		// валидация данных
		$validator = Validator::make(
		    $data,
		    [
		    	'name' => 'required',
		    ]
		);
		if ($validator->fails()) {
			return ['errors' => $validator->messages()];
		}

		// сохраняем страницу
		$customer = Customer::find($id);
		if (!$customer) {
			$customer = Customer::create($data);
		} else {
			$customer->update($data);
		}

		return ['success' => true, 'id' => $customer->id, 'row' => view('admin::customers.customer_row', ['item' => $customer])->render()];
	}

	public function postDelete($id): array
    {
		$customer = Customer::findOrFail($id);
		if ($customer->details) {
		    unlink(public_path(Customer::UPLOAD_URL) . $customer->details);
        }
		$customer->delete();

		return ['success' => true];
	}
}
