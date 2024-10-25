<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table    = 'parameters';
    protected $fillable = ['unique_code', 'parameter'];
    public $timestamps  = false;

}
