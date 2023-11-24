<?php namespace App\Traits;

use Fanky\Admin\Models\Catalog;
use Fanky\Admin\Settings;
use Illuminate\Support\Str;

trait HasSeoOptimization{

    public static $defaultTitleTemplate = '{name} купить';

    public static $defaultDescriptionTemplate = '{name} купить по выгодной цене';

    public static $defaultTextTemplate = '{name} купить по выгодной цене';

    public function getDefaultTitleTemplate() {
        if ($t = Settings::get('default_title_template')) {
            return $t;
        } else {
            return self::$defaultTitleTemplate;
        }
    }

    public function getDefaultDescriptionTemplate() {
        if ($t = Settings::get('default_description_template')) {
            return $t;
        } else {
            return self::$defaultDescriptionTemplate;
        }
    }

    public function generateTitle() {
        if (!($template = $this->getTitleTemplate())) {
            if ($this->title && $this->title != $this->name) {
                $template = $this->title;
            } else {
                $template = self::$defaultTitleTemplate;
            }
        }

        $this->title = $this->replaceTemplateVariable($template);
    }

    public function generateDescription() {
        if (!($template = $this->getDescriptionTemplate())) {
            if (!$template && $this->description) {
                $template = $this->description;
            } else {
                $template = self::$defaultDescriptionTemplate;
            }
        }

//        if (strpos($template, '{city}') === false) { //если кода city нет - добавляем
//            $template .= '{city}';
//        }

        $this->description = $this->replaceTemplateVariable($template);;
    }

    public function generateText() {
        $template = $this->getTextTemplate();
        $this->text = $this->replaceTemplateVariable($template);
    }

    public function generateKeywords() {
        if (!$this->keywords) {
            $this->keywords = mb_strtolower($this->name . ' цена, ' . $this->name . ' купить, ' . $this->name . '');
        }
    }

    private function replaceTemplateVariable($template) {
        $replace = [
            '{name}' => $this->name,
            '{h1}' => $this->h1 ?: $this->name,
            '{lower_name}' => Str::lower($this->name),
            '{article}' => $this->article,
            '{price}' => $this->price ?: 'не указана',
            '{length}' => $this->length,
            '{width}' => $this->width,
            '{height}' => $this->height,
        ];

        return str_replace(array_keys($replace), array_values($replace), $template);
    }

    public function getTitleTemplate($catalog_id = null) {
        if (!$catalog_id) $catalog_id = $this->catalog_id;
        $catalog = Catalog::find($catalog_id);
        if (!$catalog) return null;
        if (!empty($catalog->product_title_template)) return $catalog->product_title_template;
        if ($catalog->parent_id) return $this->getTitleTemplate($catalog->parent_id);

        if ($admin_template = Settings::get('default_title_template')) {
            return $admin_template;
        } else {
            return self::$defaultTitleTemplate;
        }
    }

    public function getDescriptionTemplate($catalog_id = null) {
        if (!$catalog_id) $catalog_id = $this->catalog_id;
        $catalog = Catalog::find($catalog_id);
        if (!$catalog) return null;
        if (!empty($catalog->product_description_template)) return $catalog->product_description_template;
        if ($catalog->parent_id) return $this->getDescriptionTemplate($catalog->parent_id);

        if ($admin_template = Settings::get('default_description_template')) {
            return $admin_template;
        } else {
            return self::$defaultDescriptionTemplate;
        }
    }

    public function getTextTemplate($catalog_id = null) {
        if (!$catalog_id) $catalog_id = $this->catalog_id;
        $catalog = Catalog::find($catalog_id);
        if (!$catalog) return null;
        if (!empty($catalog->product_text_template)) return $catalog->product_text_template;
        if ($catalog->parent_id) return $this->getTextTemplate($catalog->parent_id);

        return null;
    }
}
