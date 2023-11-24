<?php

namespace App\Console\Commands;

use SiteHelper;
use Carbon\Carbon;
use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Models\Complex;
use Fanky\Admin\Models\Product;
use Fanky\Admin\Models\ProductImage;
use Illuminate\Console\Command;
use DB;

class ImportOld extends Command {

	protected $signature = 'import';

	public function handle() {
//		$this->importCatalogs();
//		$this->importProducts();
		$this->importNews();
        return 0;
	}

	private function importCatalogs() {
		$olds = DB::connection('old')->table('product_categories')->get();
		foreach ($olds as $old) {
			$parent = Catalog::whereOldId($old->parent_id)->first();
			$parent_id = ($parent) ? $parent->id : 0;

			$catalog = Catalog::whereOldId($old->id)->first();
			if (!$catalog) $catalog = new Catalog();

			$data = [
				'old_id'      => $old->id,
				'parent_id'   => $parent_id,
				'alias'       => $old->alias,
				'name'        => $old->name,
				'title'       => $old->title,
				'keywords'    => $old->keywords,
				'description' => $old->description,
				'published'   => $old->show,
				'text_after'  => $old->text,
				'order'       => $old->order,
				'published'   => $old->show,
			];

			if ($old->image && !$catalog->image) {
				$url = 'http://begriff.ru/uploads/categories/' . $old->image;
				$image = SiteHelper::uploadImage($url, 'catalogs');
				if ($image) $data['image'] = $image;
			}

			$catalog->fill($data)->save();
		}
	}

	private function importProducts() {
		$olds = DB::connection('old')->table('products')->get();
		foreach ($olds as $old) {
			$parent = Catalog::whereOldId($old->category_id)->first();
			$parent_id = ($parent) ? $parent->id : 0;

			$product = Product::whereOldId($old->id)->first();
			if (!$product) $product = new Product();

			$data = [
				'old_id'      => $old->id,
				'catalog_id'  => $parent_id,
				'name'        => $old->name,
				'text'        => $old->text,
				'price'       => $old->price,
				'published'   => $old->show,
				'order'       => $old->order,
				'type_id'     => $old->type_id,
				'mount_id'    => $old->mount_id,
				'format_id'   => $old->format_id,
				'alias'       => $old->alias,
				'title'       => $old->title,
				'keywords'    => $old->keywords,
				'description' => $old->description,
			];

			$product->fill($data)->save();

			if ($old->image && !$product->image) {
				$url = 'http://begriff.ru/uploads/products/' . $old->image;
				$image = SiteHelper::uploadImage($url, 'products');
				$order = ProductImage::whereProductId($product->id)->max('order') + 1;
				ProductImage::create([
					'product_id' => $product->id,
					'image'      => $image,
					'order'      => $order
				]);
			}
		}
	}

	private function importNews() {
		$olds = DB::connection('old')->table('complex')->get();
		foreach ($olds as $old) {
			$news = Complex::whereOldId($old->id)->first();
			if (!$news) $news = new Complex();

			$data = [
				'old_id'      => $old->id,
				'published'   => ($old->show)? 1: 0,
				'date'        => Carbon::createFromTimestamp($old->date)->format('Y-m-d'),
				'name'        => $old->name,
				'announce'    => $old->announce,
				'text'        => $old->text,
				'alias'       => $old->alias,
				'title'       => (strlen($old->title)) ? $old->title : $old->name,
				'keywords'    => $old->keywords,
				'description' => $old->description,
			];

			if ($old->image && !$news->image) {
				$url = 'http://begriff.ru/uploads/complex/' . $old->image;
				$image = SiteHelper::uploadImage($url, 'complex');
				if ($image) $data['image'] = $image;
			}

			$news->fill($data)->save();
		}
	}
}
