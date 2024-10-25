<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppToken extends Model
{
    protected $fillable=['token','expires_in'];

    public function app(){
        return $this->belongsTo(MerchantApp::class,'app_id','id');
    }
}
