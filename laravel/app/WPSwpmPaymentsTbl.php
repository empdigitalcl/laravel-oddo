<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WPSwpmPaymentsTbl extends Model
{
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'member_id',
        'membership_level',
        'tbx_date',
        'tbx_id',
        'subscr_id',
        'reference',
        'payment_amount',
        'gateway',
        'status',
    ];
    protected $table = 'wp_swpm_payments_tbl';
    public $timestamps = false;

    public function scopeByMemberId($query, $memberId)
    {
        if ($memberId != null && $memberId != '') {
            return $query->where('member_id', $memberId);
        }
    }

}
