<?php namespace Fanky\Admin\Controllers;

use Fanky\Admin\Models\NewsImage;
use Request;
use Validator;
use Text;
use Fanky\Admin\Models\News;

class AdminNewsController extends AdminController {

	public function getIndex() {
		$news = News::orderBy('date', 'desc')->paginate(100);

		return view('admin::news.main', ['news' => $news]);
	}

	public function getEdit($id = null) {
		if (!$id || !($article = News::find($id))) {
			$article = new News;
			$article->date = date('Y-m-d');
			$article->published = 1;
		}

		return view('admin::news.edit', ['article' => $article]);
	}

	public function postSave() {
		$id = Request::input('id');
		$data = Request::only([
            'date', 'name', 'aside',
            'announce', 'text',
            'published', 'alias',
            'title', 'keywords',
            'description', 'on_main']);
		$image = Request::file('image');
		$type = Request::get('type');
		$data['type'] = array_get(['Новость', 'Статья', 'Акция'], $type);

		if (!array_get($data, 'alias')) $data['alias'] = Text::translit($data['name']);
		if (!array_get($data, 'title')) $data['title'] = $data['name'];
		if (!array_get($data, 'published')) $data['published'] = 0;
		if (!array_get($data, 'on_main')) $data['on_main'] = 0;

		// валидация данных
		$validator = Validator::make(
			$data,[
				'name' => 'required',
				'date' => 'required',
			]);
		if ($validator->fails()) {
			return ['errors' => $validator->messages()];
		}

		// Загружаем изображение
		if ($image) {
			$file_name = News::uploadImage($image);
			$data['image'] = $file_name;
		}

		// сохраняем страницу
		$article = News::find($id);
		$redirect = false;
		if (!$article) {
			$article = News::create($data);
			$redirect = true;
		} else {
			if ($article->image && isset($data['image'])) {
				$article->deleteImage();
			}
			$article->update($data);
		}

		if($redirect){
			return ['redirect' => route('admin.news.edit', [$article->id])];
		} else {
			return ['msg' => 'Изменения сохранены.'];
		}

	}

	public function postDelete($id) {
		$article = News::find($id);
		$article->delete();

		return ['success' => true];
	}

	public function postDeleteImage($id) {
		$news = News::find($id);
		if(!$news) return ['error' => 'news_not_found'];

		$news->deleteImage();
		$news->update(['image' => null]);

		return ['success' => true];
	}

    public function postNewsImageUpload($news_id): array
    {
        $images = Request::file('images');
        $items = [];
        if ($images) {
            foreach ($images as $image) {
                $file_name = NewsImage::uploadImage($image);
                $order = NewsImage::where('news_id', $news_id)->max('order') + 1;
                $item = NewsImage::create(['news_id' => $news_id, 'image' => $file_name, 'order' => $order]);
                $items[] = $item;
            }
        }

        $html = '';
        foreach ($items as $item) {
            $html .= view('admin::news.news_image', ['image' => $item]);
        }

        return ['html' => $html];
    }

    public function postNewsImageOrder(): array
    {
        $sorted = Request::get('sorted', []);
        foreach ($sorted as $order => $id) {
            NewsImage::whereId($id)->update(['order' => $order]);
        }

        return ['success' => true];
    }

    public function postNewsImageDelete($id): array
    {
        $item = NewsImage::findOrFail($id);
        $item->deleteImage();
        $item->delete();

        return ['success' => true];
    }
}
