<?php namespace Fanky\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model {

    protected $table = 'orders';

    protected $guarded = ['id'];

    const UPLOAD_PATH = '/public/uploads/orders/';
    const UPLOAD_URL  = '/uploads/orders/';

    public function products(): BelongsToMany
    {
        return $this->belongsToMany('Fanky\Admin\Models\Product')
            ->withPivot('count', 'price');
    }

    public function dateFormat($format = 'd.m.Y')
    {
        if (!$this->created_at) return null;
        return date($format, strtotime($this->created_at));
    }

    public function scopeNewOrder($query) {
        return $query->whereNew(1);
    }

}
