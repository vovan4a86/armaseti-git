<?php
namespace Fanky\Admin\Models;

use App\Traits\HasFile;
use App\Traits\HasH1;
use App\Traits\HasImage;
use App\Traits\HasSeo;
use App\Traits\HasSeoOptimization;
use App\Traits\HasTopView;
use App\Traits\OgGenerate;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use URL;

/**
 * @property HasMany|Collection $public_children
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $h1
 * @property string $keywords
 * @property string $description
 * @property string $og_title
 * @property string $og_description
 * @property string $image
 * @property string $text
 * @property string $alias
 * @property string $slug
 * @property string $title
 * @property int $order
 * @property bool $published
 * @property bool $on_main
 * @property bool $on_menu
 * @property bool $on_main_list
 * @property bool $on_top_menu
 * @property bool $on_footer_menu
 * @mixin \Eloquent
 * @method static whereParentId(int|mixed $id)
 * @method static Builder whereAlias($value)
 * @method static Builder whereCatalogId($value)
 * @method static Builder whereId($value)
 * @method static Builder whereName($value)
 * @method static Builder whereProductId($value)
 * @method static Builder whereValue($value)
 */
class Page extends Model
{
    use HasImage, HasFile, OgGenerate, HasSeo, HasH1, HasSeoOptimization, HasTopView;

    protected $guarded = ['id'];

    const UPLOAD_URL = '/uploads/pages/';
    const TOP_VIEW_DEFAULT = '/static/images/common/top-view.jpg';

    private $_disableEventUpdateSlug;
    private $_disableEventUpdatePublished;

    public static $thumbs = [
        1 => '100x100', //admin
    ];

    public static $thumbs_top_view = [
        1 => '100x100', //admin
        2 => '1920x311', //top view
    ];

    protected $table = 'pages';
    protected $_parents = [];
    private $_url;

    //страницы без региональности
    public static $excludeRegionAlias = [
        'ajax',
        'about',
        'reviews',
        'policy',
        'partners',
        'cart',
        'search',
        'contacts',
        'payments',
        'news',
        'handbook',
        'reviews'
    ];

    //региональность пока только для каталога
    public static $regionAliases = [
        'catalog'
    ];

    public static $hasImageField = [];

    public static $excludeTextField = [
        'news',
        'reviews',
        'contacts',
        'handbook'
    ];

    public static function boot()
    {
        parent::boot();

        self::saved(
            function (self $category) {
                if ($category->isDirty('alias') || $category->isDirty('parent_id')) {
                    if (!$category->_disableEventUpdateSlug) {
                        self::updateUrlRecurse($category);
                    }
                }
                if ($category->isDirty('published') && $category->published == 0) {
                    if (!$category->_disableEventUpdatePublished) {
                        self::updateDisablePublishedRecurse($category);
                    }
                }
            }
        );
    }

    public static function updateUrlRecurse(self $category)
    {
        $parents = $category->getParents(true, true);
        $slug_arr = [];
        foreach ($parents as $parent) {
            $slug_arr[] = $parent->alias;
        }
        //чтобы событие на обновление не сработало
        $category->_disableEventUpdateSlug = true;
        $category->update(['slug' => implode('/', $slug_arr)]);
        foreach ($category->children()->get() as $child) {
            self::updateUrlRecurse($child);
        }
    }

    public static function updateDisablePublishedRecurse(self $category)
    {
        //чтобы событие на обновление не сработало
        $category->_disableEventUpdatePublished = true;
        $category->update(['published' => 0]);
        foreach ($category->children()->get() as $child) {
            self::updateUrlRecurse($child);
        }
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo('Fanky\Admin\Models\Page', 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany('Fanky\Admin\Models\Page', 'parent_id');
    }

    public function public_children(): HasMany
    {
        return $this->children()
            ->where('published', 1)
            ->orderBy('order');
    }

    public function settingGroups(): HasMany
    {
        return $this->hasMany('Fanky\Admin\Models\SettingGroup', 'page_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany('Fanky\Admin\Models\Gallery', 'page_id');
    }

    public function catalog(): HasOne
    {
        return $this->hasOne('Fanky\Admin\Models\Catalog', 'page_id');
    }

    public function scopePublic($query)
    {
        return $query->where('published', 1);
    }

    public function scopeMain($query)
    {
        return $query->where('parent_id', 1);
    }

    public function getUrlAttribute(): string
    {
        if ($this->_url) {
            return $this->_url;
        }

        $path = [$this->alias];
        if (!count($this->_parents)) {
            $this->getParents();
        }

        foreach ($this->_parents as $parent) {
            $path[] = $parent->alias;
        }
        $path = implode('/', array_reverse($path));

        $city_alias = session('city_alias');

        if ($city_alias && in_array($this->alias, self::$regionAliases)) {
            $path = $city_alias . '/' . ltrim($path, '/');
        }

        if (!$path) {
            $path = $city_alias;
        }

        $this->_url = route('default', ['alias' => $path]);

        return $this->_url;
    }

    public function getIsActiveAttribute(): bool
    {
        //берем или весь или часть адреса, для родительских страниц
        //исключение страница каталога
//		if ($this->alias == 'catalog') {
//			$url = URL::current();
//		} else {
//			$url = substr(URL::current(), 0, strlen($this->getUrlAttribute()));
//		}
        $url = URL::current();

        return ($url == $this->getUrlAttribute());
    }

    /**
     * Братья/сестры
     *
     * @return mixed
     */
    public function siblings()
    {
        return self::whereParentId($this->parent_id);
    }

    /**
     * @param string[] $path
     *
     * @return Page
     */
    public static function getByPath($path, $id = 1)
    {
        $parent_id = $id;
        $page = null;

        /* проверка по пути */
        foreach ($path as $alias) {
            $page = Page::whereAlias($alias)
                ->whereParentId($parent_id)
                ->public()
                ->get(['id', 'alias', 'parent_id'])
                ->first();
            if ($page === null) {
                return null;
            }
            $parent_id = $page->id;
        }

        if ($page && $page->id > 0) {
            return Page::find($page->id);
        } else {
            return null;
        }
    }

    public function getParents($with_self = false, $reverse = false): array
    {
        $p = $this;
        $parents = [];
        if ($with_self) {
            $parents[] = $p;
        }
        if (!count($this->_parents) && $this->parent_id > 1) {
            while ($p && $p->parent_id > 1) {
                $p = self::getPages(
                    $p->parent_id
                ); // Page::find($p->parent_id, ['id','parent_id','name','alias','published']);
                $this->_parents[] = $p;
            }
        }
        $parents = array_merge($parents, $this->_parents);
        if ($reverse) {
            $parents = array_reverse($parents);
        }

        return $parents;
    }

    public static function getPages($id = null)
    {
        $pages = Cache::get('pages', []);
        if (!$pages) {
            $pages_arr = Page::all(['id', 'name', 'alias', 'published', 'parent_id']);
            foreach ($pages_arr as $item) {
                $pages[$item->id] = $item;
            }
            Cache::add('pages', $pages, 1);
        }
        if ($id) {
            return (isset($pages[$id])) ? $pages[$id] : null;
        } else {
            return $pages;
        }
    }

    public function getBread(): array
    {
        $bread = [];

        foreach ($this->getParents(true) as $p) {
            $bread[] = [
                'url' => $p->url,
                'name' => $p->name
            ];
        }

        return array_reverse($bread);
    }

    public function getPublicChildren()
    {
        return $this->children()->public()->orderBy('order')->get();
    }

    public function getPublicChildrenIds()
    {
        return $this->children()->public()->orderBy('order')->pluck('id')->all();
    }

    public function getRecurseChildrenIds(self $parent = null): array
    {
        if (!$parent) {
            $parent = $this;
        }
        $ids = self::query()->where('slug', 'like', $parent->slug . '%')
            ->pluck('id')->all();

        return $ids;
    }

    public static function getRecursePages($parent_id)
    {
        $pages = Page::whereParentId($parent_id)->pluck('id')->all();
        if (!count($pages)) {
            return [];
        }
        $result = $pages;
        foreach ($pages as $id) {
            $children = self::getRecursePages($id);
            if (count($children)) {
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }

    public function getPublicChildrenFooter()
    {
        return $this->children()->public()->where('on_footer_menu', 1)->orderBy('order')->get();
    }

    public function getAdditionalClassesAttribute()
    {
        return array_get(self::$page_classes, $this->id);
    }

    public function delete()
    {
        $this->deleteImage();
        foreach ($this->children as $child) {
            $child->delete();
        }

        parent::delete();
    }

    public function getLastModifed()
    {
        /** @var Carbon $updated */
        return $this->updated_at;
    }

    public function getView(): string
    {
        $view = 'pages.text';
        if (view()->exists('pages.unique.' . $this->alias)) {
            $view = 'pages.unique.' . $this->alias;
        }

        return $view;
    }

    private $_is_region = null;

    public function getIsRegionAttribute()
    {
        if ($this->_is_region !== null) {
            return $this->_is_region;
        }
        $parents = $this->getParents();

        if (!$parents && in_array($this->alias, getCityAliases())) {
            $this->_is_region = true;
        } else {
            $root = array_pop($parents);
            $this->_is_region = (bool)($root && !in_array($root->alias, self::$excludeRegionAlias));
        }

        return $this->_is_region;
    }

    public function getBackgroundType()
    {
        if (!$this->image) {
            return 0;
        }
        $array = explode('.', $this->image);
        $ext = array_pop($array);
        if ($ext == 'mp4') {
            return 2;
        }

        return 1;
    }
}
