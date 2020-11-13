<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WPPosts extends Model
{
    protected $fillable = [
        'user_id',
        'meta_key',
        'meta_value',
    ];
    protected $table = 'wp_posts';
    public $timestamps = false;

    public function items()
    {
        return $this->hasMany('App\WPWCOrderItems', 'order_id', 'ID');
    }
    public function metaValues()
    {
        return $this->hasMany('App\WPPostMeta', 'post_id', 'ID');
    }

    public function total()
    {
        return $this->hasOne('App\WPPostMeta', 'post_id', 'ID')->where('meta_key', '_order_total');
    }
    public function customerUserId()
    {
        return $this->hasOne('App\WPPostMeta', 'post_id', 'ID')->where('meta_key', '_customer_user');
    }
    public function customerEmail()
    {
        return $this->hasOne('App\WPPostMeta', 'post_id', 'ID')->where('meta_key', '_billing_email');
    }
    public function webpayToken()
    {
        return $this->hasOne('App\WPPostMeta', 'post_id', 'ID')->where('meta_key', '_webpay_token');
    }
    /* public function total()
    {
        return $this->hasOne('App\WPPostMeta', 'post_id', 'ID')->where('meta_key', '_order_total');
    } */

    public function scopeOrders($query) {
        $postType = 'shop_order';
        return $query->where('post_type', $postType);
    }
    public function scopeByWebpayToken($query, $token) {
        return $query->whereHas('metaValues', function($query) use ($token) {
            return $query->where('meta_key', '_webpay_token');
        });
    }

}
