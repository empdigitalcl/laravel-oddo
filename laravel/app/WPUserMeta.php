<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WPUserMeta extends Model
{
    protected $fillable = [
        'user_id',
        'meta_key',
        'meta_value',
    ];
    protected $table = 'wp_usermeta';
    public $timestamps = false;

    public function scopeByUserId($query, $userId)
    {
        if ($userId != null && $userId != '') {
            return $query->where('user_id', $userId);
        }
    }
    public function scopeByMetaKey($query, $metaKey)
    {
        if ($metaKey != null && $metaKey != '') {
            return $query->where('meta_key', $metaKey);
        }
    }


}
