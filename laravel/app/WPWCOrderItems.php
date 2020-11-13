<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WPWCOrderItems extends Model
{
    protected $fillable = [];
    protected $table = 'wp_woocommerce_order_items';
    public $timestamps = false;

    public function metaValues()
    {
        return $this->hasMany('App\WPWCOrderItemMeta', 'order_item_id', 'order_item_id');
    }

}
