<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;

class Preference extends Model
{
	protected $table = 'preferences';
    protected $fillable = ['category', 'field', 'value'];
    public $timestamps = false;

    /*FOR CACHE - BELOW*/
    public static function getAll()
    {
        $data = Cache::get(config('cache.prefix') . '-preferences');
        if (empty($data)) {
            $data = parent::all();
            Cache::put(config('cache.prefix') . '-preferences', $data, 30 * 86400);
        }

        return $data;
    }
}
