<?php
namespace Fanky\Admin\Controllers;

use Fanky\Admin\Models\Customer;
use Fanky\Admin\Pagination;
use Request;
use Text;
use Validator;

class AdminCustomersController extends AdminController
{

    public function getIndex()
    {
        $q = request()->get('q');

        if (!$q) {
            $customers = Pagination::init(Customer::orderBy('created_at', 'desc'), 30)->get();
        } else {
            $query = Customer::where('name', 'LIKE', '%' . $q . '%')
                ->orWhere('email', 'LIKE', '%' . $q . '%')
                ->orWhere('phone', 'LIKE', '%' . $q . '%')
                ->orWhere('company', 'LIKE', '%' . $q . '%')
                ->orWhere('inn', 'LIKE', '%' . $q . '%')
                ->orderBy('created_at', 'desc');
            $customers = Pagination::init($query, 30)->get();
        }


        return view('admin::customers.main', ['customers' => $customers]);
    }

    public function getEdit($id = null)
    {
        if (!$id || !($customer = Customer::findOrFail($id))) {
            $customer = new Customer;
            $customer->published = 1;
        }

        return view('admin::customers.edit', ['customer' => $customer]);
    }

    public function postSave()
    {
        $id = Request::input('id');
        $data = Request::only(
            ['name', 'alias', 'text', 'image', 'announce', 'text', 'date', 'published', 'order', 'on_main']
        );
        $image = Request::file('image');

        if (!array_get($data, 'alias')) {
            $data['alias'] = Text::translit($data['name']);
        }
        if (!array_get($data, 'published')) {
            $data['published'] = 0;
        }
        if (!array_get($data, 'on_main')) {
            $data['on_main'] = 0;
        }

        $rules = [
            'date' => 'required',
            'name' => 'required',
            'text' => 'required',
        ];
        $rules['alias'] = $id
            ? 'required|unique:customers,alias,' . $id . ',id'
            : 'required|unique:customers,alias,null,id';

        // валидация данных
        $validator = Validator::make(
            $data,
            $rules
        );
        if ($validator->fails()) {
            return ['errors' => $validator->messages()];
        }

        if ($image) {
            if ($image->getClientOriginalExtension() == 'svg') {
                $file_name = Customer::uploadIcon($image);
            } else {
                $file_name = Customer::uploadImage($image);
            }
            $data['image'] = $file_name;
        }

        // сохраняем страницу
        $customer = Customer::find($id);
        if (!$customer) {
            $data['order'] = Customer::max('order') + 1;
            $customer = Customer::create($data);
            return ['redirect' => route('admin.customers.edit', [$customer->id])];
        } else {
            if ($customer->image && isset($data['image'])) {
                $customer->deleteImage();
            }
            $customer->update($data);
        }

        return ['msg' => 'Изменения сохранены.'];
    }

    public function postDelete($id)
    {
        $customer = Customer::find($id);
        $customer->delete();

        return ['success' => true];
    }

    public function postAddToFavorite()
    {
        $id = request()->get('id');

        if(!$id) return ['success' => false, 'msg' => 'ID не передан'];

        $customer = Customer::find($id);

        $customer->timestamps = false;
        if ($customer->is_favorite) {
            $customer->update(['is_favorite' => 0]);
        } else {
            $customer->update(['is_favorite' => 1]);
        }

        return ['success' => true];

    }
}
