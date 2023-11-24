<?php namespace App\Traits;

use Fanky\Admin\Models\Catalog;

trait HasTopView {
    public function getTopView()
    {
        return $this->top_view ? $this->top_view(2) : self::TOP_VIEW_DEFAULT;
    }

    public function getCatalogTopView()
    {
        $catalog = $this;
        if ($catalog->parent_id == 0) {
            return $catalog->top_view ? $catalog->top_view(2) : self::TOP_VIEW_DEFAULT;
        }
        while ($catalog->parent_id > 0) {
            if ($catalog->parent_id > 0 && $catalog->top_view) {
                return $catalog->top_view(2);
            }

            $catalog = Catalog::find($catalog->parent_id);
        }

        return $catalog->top_view ? $catalog->top_view(2) : self::TOP_VIEW_DEFAULT;

    }
}
