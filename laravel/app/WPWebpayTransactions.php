<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WPWebpayTransactions extends Model
{
    protected $fillable = [
        'order_id',
        'buy_order',
        'amount',
        'token',
        'session_id',
        'status',
        'transbank_response',
    ];
    protected $table = 'wp_webpay_transactions';
    public $timestamps = false;

    public function scopeByToken($query, $token)
    {
        if ($token != null && $token != '') {
            return $query->where('token', $token);
        }
    }
    public function scopeByOrderId($query, $orderId)
    {
        if ($orderId != null && $orderId != '') {
            return $query->where('order_id', $orderId);
        }
    }


}
