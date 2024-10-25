<?php

namespace Modules\Upgrader\Entities\Handler;

class Json
{
    /**
     * The updater json array
     *
     * @var array
     */
    protected $updater = [];

    /**
     * The default updater json array
     *
     * @var array
     */
    protected $default = [
        'version' => null,
        'supported_versions' => [
            "0.0.0"
        ],
        'archive' => null,
        'description' => null,
        'delete' => [],
    ];

    /**
     * Create a new instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->updater = $this->updaterJson();
    }

    /**
     * Get the updater json value by key or return the whole array
     *
     * @param string $key
     * @param string $default
     * @return array
     */
    public function get($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->updater;
        }
        
        return $this->updater[$key] ?? $default;
    }

    /**
     * Get the updater json array, if not exists return the default array
     *
     * @return array
     */
    protected function updaterJson()
    {
        $path = storage_path('updates/updater.json');
        
        if (!file_exists($path)) {
            return $this->default;
        }

        return array_merge($this->default, json_decode(file_get_contents($path), true));
    }
}