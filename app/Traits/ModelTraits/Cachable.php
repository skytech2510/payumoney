<?php

namespace App\Traits\ModelTraits;

use Cache;

trait Cachable
{
    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::updated(function($model){
            self::forgetCache();
        });

        self::created(function($model){
            self::forgetCache();
        });

        self::deleted(function($model){
            self::forgetCache();
        });
    }

    /**
     * getAll
     *
     * @return collection
     */
    public static function getAll()
    {
        return self::setCache(function() {
            return parent::all();
        });
    }

    /**
     * setCache
     *
     * @param  mixed $callback
     * @param  mixed $cacheKey
     * @return void
     */
    public static function setCache(\Closure $callback, $cacheKey = null)
    {
        if (is_null($cacheKey)) {
            $cacheKey = self::cacheKey();
        }

        return Cache::remember($cacheKey, self::cacheTimestamp(), $callback);
    }

    /**
     * forgetCache
     *
     * @param  mixed $cacheKey
     * @return void
     */
    public static function forgetCache($cacheKey = null)
    {
        if (is_null($cacheKey)) {
            Cache::forget(self::cacheKey());
        }

        if (is_string($cacheKey)) {
            Cache::forget(self::cacheKey($cacheKey));
        }

        if (is_array($cacheKey)) {
            foreach ($cacheKey as $key => $value) {
                Cache::forget(self::cacheKey($value));
            }
        }
    }

    /**
     * getTableName
     *
     * @return void
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * cacheKey
     *
     * @param  mixed $cacheTag
     * @return void
     */
    public static function cacheKey($cacheTag = null)
    {
        if (is_null($cacheTag)) {
            return config('cache.prefix') . '.' . self::getTableName();
        }

        return config('cache.prefix') . '.' . $cacheTag;
    }

    /**
     * cacheTimestamp
     *
     * @param  mixed $timestamp
     * @return void
     */
    protected static function cacheTimestamp($timestamp = null)
    {
        if (is_null($timestamp)) {
            return 30 * 86400;
        }

        return $timestamp;
    }
}
