<?php namespace Fanky\Admin\Controllers;

use Request;
use Text;
use Validator;
use DB;
use Fanky\Admin\Models\Review;

class AdminReviewsController extends AdminController {

	public function getIndex()
	{
		$reviews = Review::orderBy('order')->get();

		return view('admin::reviews.main', ['reviews' => $reviews]);
	}

	public function getEdit($id = null)
	{
		if (!$id || !($review = Review::findOrFail($id))) {
			$review = new Review;
			$review->published = 1;
		}

		return view('admin::reviews.edit', ['review' => $review]);
	}

	public function postSave()
	{
		$id = Request::input('id');
		$data = Request::only(['name', 'alias', 'text', 'image', 'announce', 'text', 'date', 'published', 'order', 'on_main']);
		$image = Request::file('image');

        if (!array_get($data, 'alias')) $data['alias'] = Text::translit($data['name']);
        if (!array_get($data, 'published')) $data['published'] = 0;
		if (!array_get($data, 'on_main')) $data['on_main'] = 0;

        $rules = [
            'date' => 'required',
            'name' => 'required',
            'text' => 'required',
        ];
        $rules['alias'] = $id
            ? 'required|unique:reviews,alias,' . $id . ',id'
            : 'required|unique:reviews,alias,null,id';

		// валидация данных
		$validator = Validator::make(
		    $data,
		    $rules
		);
		if ($validator->fails()) {
			return ['errors' => $validator->messages()];
		}

        if ($image) {
            if($image->getClientOriginalExtension() == 'svg') {
                $file_name = Review::uploadIcon($image);
            } else {
                $file_name = Review::uploadImage($image);
            }
            $data['image'] = $file_name;
        }

		// сохраняем страницу
		$review = Review::find($id);
		if (!$review) {
			$data['order'] = Review::max('order') + 1;
			$review = Review::create($data);
			return ['redirect' => route('admin.reviews.edit', [$review->id])];
		} else {
            if ($review->image && isset($data['image'])) {
                $review->deleteImage();
            }
			$review->update($data);
		}

		return ['msg' => 'Изменения сохранены.'];
	}

	public function postReorder()
	{
		$sorted = Request::input('sorted', []);
		foreach ($sorted as $order => $id) {
			DB::table('reviews')->where('id', $id)->update(array('order' => $order));
		}
		return ['success' => true];
	}

	public function postDelete($id)
	{
		$review = Review::find($id);
		$review->delete();

		return ['success' => true];
	}

    public function postDeleteImage($id) {
        $review = Review::find($id);
        if(!$review) return ['errors' => 'review_not_found'];

        $review->deleteImage();
        $review->update(['image' => null]);

        return ['success' => true];
    }

}
