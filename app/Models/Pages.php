<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
    protected $table    = 'pages';
    protected $casts    = ['position' => 'array'];
    protected $fillable = [
        'name',
        'url',
        'content',
        'position',
        'status',
    ];
}
