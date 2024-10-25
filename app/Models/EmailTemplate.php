<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    public $timestamps = false;

    protected $fillable = [
        'temp_id',
        'subject',
        'body',
        'lang',
        'type',
        'language_id',
    ];

    public function scopeTempId($query, $value)
    {
        return $query->where('temp_id', $value);
    }

    public function scopeAlias($query, $value)
    {
        return $query->where('alias', $value);
    }

    public function scopeType($query, $value)
    {
        return $query->where('type', $value);
    }

    public function scopeEnglishLanguage($query)
    {
        return $query->where('lang', "en");
    }

    public function scopeDefaultLanguage($query)
    {
        return $query->where('language_id', settings('default_language'));
    }

}
