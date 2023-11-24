<?php

use Illuminate\Database\Seeder;

class ObjectsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages')->insertOrIgnore(
            [
                'parent_id' => 1,
                'name' => 'Объекты',
                'h1' => 'Наши объекты',
                'alias' => 'objects',
                'slug' => 'objects',
                'title' => 'Объекты',
                'order' => 3,
                'published' => 1,
                'on_footer_menu' => 1
            ]
        );
    }
}
