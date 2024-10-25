<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $table = 'metas';

    protected $fillable = [
        'url',
        'title',
        'description',
        'keywords',
    ];

    public $timestamps = false;
}
