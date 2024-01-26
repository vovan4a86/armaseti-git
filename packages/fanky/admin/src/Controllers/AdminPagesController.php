<?php namespace Fanky\Admin\Controllers;

use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\SearchIndex;
use Fanky\Auth\Auth;
use Illuminate\Support\Str;
use Request;
use Validator;
use Text;
use DB;
use Fanky\Admin\Models\Page;
use Fanky\Admin\Models\Setting;
use SiteHelper;

class AdminPagesController extends AdminController {

	public function getIndex() {
		$sitemap = Request::get('sitemap', null);
		$search = Request::get('update_search', null);
		$cache = Request::get('clear_cache', null);
		$content = '';
		if ($sitemap) {
			SiteHelper::generateSitemap();
			$content = view('admin::pages.sitemap');
		}
		if ($search) {
			SearchIndex::update_index();
			$content = view('admin::pages.search');
		}
		if ($cache) {
			\Cache::flush();
			$content = view('admin::pages.cache');
		}
		$pages = Page::orderBy('order')->get();

		return view('admin::pages.main', ['pages' => $pages, 'content' => $content]);
	}

	public function postEdit($id = null) {
		if (!$id || !($page = Page::findOrFail($id))) {
			$page = new Page;
			$page->parent_id = Request::input('parent');
			$page->published = 1;
		}
		// Загружаем настройки, если есть
		$setting_groups = [];
		if ($page->id) {
			$setting_groups = $page->settingGroups()->orderBy('order')->get();
		}
		$galleries = [];
		if ($page->id) {
			$galleries = $page->galleries()->orderBy('order')->get();
		}
		// Загружаем галереи, если есть

		$pages = $this->getPageRecurse();

		return view('admin::pages.edit', [
            'page' => $page,
            'pages' => $pages,
            'setting_groups' => $setting_groups,
            'galleries' => $galleries,
        ]);
	}

	private function getPageRecurse($parent_id = 0, $lvl = 0){
		$result = [];
		$pages = Page::whereParentId($parent_id)->orderBy('order')->get();
		foreach ($pages as $page){
			$result[$page->id] = str_repeat('&mdash;', $lvl) . $page->name;
			$result = $result + $this->getPageRecurse($page->id, $lvl+1);
		}

		return $result;
	}

	public function getEdit($id = null) {
		$pages = Page::orderBy('order')->get();

		return view('admin::pages.main', ['pages' => $pages, 'content' => $this->postEdit($id)]);
	}

	public function postSave() {
        if (!Auth::user()->isAdmin) return ['alert' => 'Не хватает прав на действие!'];

        $id = Request::input('id');
		$data = Request::except(['setting', 'setting_group']);
		$data = array_filter($data, function($key){
			return !Str::startsWith($key, 'setting_file_');
		}, ARRAY_FILTER_USE_KEY);
		$image = Request::file('image');
		if (!array_get($data,'published')) $data['published'] = 0;
		if (!array_get($data,'on_header')) $data['on_header'] = 0;
		if (!array_get($data,'on_footer')) $data['on_footer'] = 0;
		if (!array_get($data,'on_mobile')) $data['on_mobile'] = 0;

		$page = Page::find($id);

		// Определяем правила валидации
		$rules = [
			'name' => 'required',
		];
		if ($page && $page->system == 0) $rules['alias'] = 'required|unique:pages,alias,' . $page->id;
		elseif (!$page && $data['alias']) $rules['alias'] = 'required|unique:pages';

		// валидация данных
		$validator = Validator::make($data, $rules);
		if ($validator->fails()) {
			return ['errors' => $validator->messages()];
		}
		// Загружаем изображение
		if ($image) {
            $file_name = Page::uploadImage($image);
			$data['image'] = $file_name;
		}

		// сохраняем страницу
		if (!$page) {
			$check_alias = false;
			if (!$data['alias']) {
				$data['alias'] = Text::translit($data['name']);
				$check_alias = DB::table('pages')->where('alias', $data['alias'])->first();
			}
			if (!$data['title']) $data['title'] = $data['name'];
			$data['order'] = Page::where('parent_id', $data['parent_id'])->max('order') + 1;
			$page = Page::create($data);
			if ($check_alias) {
				$page->alias = $page->id . '-' . $page->alias;
				$page->save();
			}

			return ['redirect' => route('admin.pages.edit', [$page->id])];
		} else {
			if ($page->system == 1) unset($data['alias']);
			if ($page->parent_id != $data['parent_id']) $data['order'] = Page::where('parent_id', $data['parent_id'])->max('order') + 1;
			if ($page->image && isset($data['image'])) {
				$page->deleteImage();
			}
            if ($page->top_view && isset($data['top_view'])) {
                $page->deleteTopView();
            }
			$page->update($data);

			// сохраняем настройки
			$groups = Request::input('setting_group', []);
			if (!empty($groups)) {
				$settings_data = Request::input('setting', []);
				$settings = Setting::whereIn('group_id', $groups)->get();
				foreach ($settings as $setting) {
					AdminSettingsController::settingSave($setting, array_get($settings_data, $setting->id));
				}
				if (!empty($_FILES)) return ['redirect' => route('admin.pages.edit', [$page->id])];
			}
		}

		return [
		    'success' => true,
            'msg' => 'Изменения сохранены',
            'row' => view('admin::pages.tree_item', ['item' => $page])->render()
        ];
	}

	public function postReorder() {
		// изменение родителя
		$id = Request::input('id');
		$parent = Request::input('parent');
		DB::table('pages')->where('id', $id)->update(array('parent_id' => $parent));
		// сортировка
		$sorted = Request::input('sorted', []);
		foreach ($sorted as $order => $id) {
			DB::table('pages')->where('id', $id)->update(array('order' => $order));
		}

		return ['success' => true];
	}

	public function postDelete($id) {
        if (!Auth::user()->isAdmin) return ['alert' => 'Не хватает прав на действие!'];

        $page = Page::findOrFail($id);
		if ($page->system == 1) {
			return ['success' => false, 'msg' => 'Невозможно удалить системную страницу!'];
		}
		$page->delete();

		return ['success' => true];
	}

	public function getGetPages($id = 0) {
		$pages = Page::whereParentId($id)->orderBy('order')->get();
		$result = [];
		foreach ($pages as $page) {
			$has_children = ($page->children()->count()) ? true : false;
			$result[] = [
				'id'       => $page->id,
				'text'     => str_limit($page->name, 35),
				'children' => $has_children,
				'icon'     => ($page->published) ? 'fa fa-eye text-green' : 'fa fa-eye-slash text-muted',
			];
		}

		return $result;
	}

	public function getFileManager() {
		return view('admin::pages.filemanager');
	}

	public function getImageManager() {
		return view('admin::pages.imagemanager');
	}
}
