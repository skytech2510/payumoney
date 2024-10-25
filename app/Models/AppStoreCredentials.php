<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppStoreCredentials extends Model
{
    protected $table = 'app_store_credentials';

    protected $fillable = [
        'has_app_credentials', 'link','logo','company',
    ];
}
