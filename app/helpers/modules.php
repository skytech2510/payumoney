<?php

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Cache;

function module(String $name = null)
{
    /**
     * Find a single module or collection
     *
     * @param string $name
     *
     * @return collection
     */
    if (is_null($name)) {
        return \Nwidart\Modules\Facades\Module::all();
    }

    return \Nwidart\Modules\Facades\Module::find($name);
}

if (!function_exists('getAllModules')) {
    /**
     * Get all available modules.
     *
     * @return array
     */
    function getAllModules(): Array
    {
        return Module::all();
    }
}

if (!function_exists('getAllModulesName')) {
    /**
     * Get all available modules name.
     *
     * @return array
     */
    function getAllModulesName(): Array
    {
        return array_keys(getAllModules());
    }
}

if (!function_exists('findModuleByName')) {    
    /**
     * find Module By Name
     *
     * @param mixed $name
     * @return void
     */
    function findModuleByName(string $name)
    {
        return Module::find($name);
    }
}

if (!function_exists('hasModule')) {
    /**
     * Check if a module exists.
     *
     * @param  string  $name
     * @return bool
     */
    function hasModule(string $name): bool
    {
        return Module::has($name);
    }
}


if (!function_exists('getCoreModule')) {
    /**
     * Get the core module instance.
     *
     * @param  string  $name
     * @return array
     */
    function getCoreModule(string $name = null)
    {
        if (!empty($name)) {
            return findModuleByName($name);
        }

        return array_filter(getAllModules(), function ($coreModule) {
            return $coreModule->get('core');
        });
    }
}

if (!function_exists('getCustomModules')) {
    /**
     * get custom modules based on a condition.
     *
     * @return array
     */
    function getCustomModules(): array
    {
        return array_filter(getAllModules(), function ($coreModule) {
            return !$coreModule->get('core');
        });
    }
}

if (!function_exists('getActiveModules')) {
    /**
     * get Active modules.
     *
     * @return array
     */
    function getActiveModules(): array
    {
        return Module::getByStatus(1);
    }
}

if (!function_exists('getActiveCustomModules')) {
    /**
     * get active custom modules based on a condition.
     *
     * @return array
     */
    function getActiveCustomModules(): array
    {
        return array_filter(getActiveModules(), function ($coreModule) {
            return !$coreModule->get('core');
        });
    }
}

function isActive(String $name = null)
{
    /**
     * Checking if module active or not
     *
     * @param string $name
     *
     * @return bool
     */
    if (is_null($name)) {
        return \Nwidart\Modules\Facades\Module::collections();
    }

    return \Nwidart\Modules\Facades\Module::collections()->has($name);
}

if (!function_exists('getCustomAddons')) {    
    /**
     * getCustomAddons
     *
     * @return void
     */
    function getCustomAddonNames()
    {
        $activeAddons = \Modules\Addons\Entities\Addon::getByStatus(1);
        $customAddons = array_filter($activeAddons, function ($activeAddon) {
            return !$activeAddon->get('core');
        });
        return array_keys($customAddons);
    }
}

if (!function_exists('getAddons')) {    
    /**
     * getAddons
     *
     * @return void
     */
    function getAddons()
    {
        $allAddon = \Modules\Addons\Entities\Addon::getByStatus(1);
        return array_keys($allAddon);
    }
}

if (!function_exists('m_ins_ckr')) {
    /**
     * Module periodical checker
     */
    function m_ins_ckr($mns) {
		Cache::put(base64_decode($mns), env(base64_decode($mns)), 2629746);
		return false;
    }

}