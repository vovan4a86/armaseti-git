<?php namespace Fanky\Admin\Controllers;

use Fanky\Admin\Models\Handbook;
use Illuminate\Support\Facades\DB;
use Request;
use Validator;
use Text;
use Fanky\Admin\Models\News;

class AdminHandbookController extends AdminController {

	public function getIndex() {
		$items = Handbook::orderBy('order')->paginate(100);

		return view('admin::handbook.main', ['items' => $items]);
	}

	public function getEdit($id = null) {
		if (!$id || !($article = Handbook::find($id))) {
			$article = new Handbook;
			$article->published = 1;
			$article->order = Handbook::max('order') + 1;
		}

		return view('admin::handbook.edit', ['article' => $article]);
	}

	public function postSave() {
		$id = Request::input('id');
		$data = Request::only(['name', 'text', 'published', 'alias', 'title', 'keywords', 'description', 'og_title', 'og_description']);

		if (!array_get($data, 'alias')) $data['alias'] = Text::translit($data['name']);
		if (!array_get($data, 'title')) $data['title'] = $data['name'];
		if (!array_get($data, 'published')) $data['published'] = 0;

		// валидация данных
		$validator = Validator::make(
			$data,[
				'name' => 'required',
			]);
		if ($validator->fails()) {
			return ['errors' => $validator->messages()];
		}

		// сохраняем страницу
		$article = Handbook::find($id);
		$redirect = false;
		if (!$article) {
			$article = Handbook::create($data);
			$redirect = true;
		} else {
			$article->update($data);
		}

		if($redirect){
			return ['redirect' => route('admin.handbook.edit', [$article->id])];
		} else {
			return ['msg' => 'Изменения сохранены.'];
		}

	}

	public function postDelete($id) {
		$article = Handbook::find($id);
		$article->delete();

		return ['success' => true];
	}

    public function postReorder()
    {
        $sorted = Request::input('sorted', []);
        foreach ($sorted as $order => $id) {
            DB::table('handbook')->where('id', $id)->update(array('order' => $order));
        }
        return ['success' => true];
    }

}
