<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WPPostMeta extends Model
{
    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
    ];
    protected $table = 'wp_postmeta';
    public $timestamps = false;

    public function scopeByMetaKey($query, $metaKey)
    {
        if ($metaKey != null && $metaKey != '') {
            return $query->where('meta_key', $metaKey);
        }
    }
}
