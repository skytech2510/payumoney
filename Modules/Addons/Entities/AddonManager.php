<?php

namespace Modules\Addons\Entities;

use Artisan, File, ZipArchive, Exception;
use Modules\Addons\Entities\Addon;
use Illuminate\Http\Request;

class AddonManager
{
    /**
     * upload
     *
     * @param  request $addonZip
     * @return collection
     */
    public static function upload($addonZip)
    {
        try {
            if (!class_exists('ZipArchive')) {
                return [
                    'status' => 201,
                    'message' => __('ZipArchive class does not exist.'),
                ];
            }
    
            $zipped_file_name = pathinfo($addonZip->getClientOriginalName(), PATHINFO_FILENAME);
            
            $zip = new ZipArchive;
            $res = $zip->open($addonZip);
    
            if ($res === true) {
                if(!File::isDirectory(base_path('temp'))){
                    File::makeDirectory(base_path('temp'), config('addons.file_permission'), true, true);
                }
                (new \Illuminate\Filesystem\Filesystem)->cleanDirectory(base_path('temp'));
    
                $res = $zip->extractTo(base_path('temp'));
                $zip->close();
            }
            $tempModulePath = base_path('temp') .'/'. $zipped_file_name . '/';
            $tempFilePaths = [
                'Config/config.php',
                'Providers/' . $zipped_file_name . 'ServiceProvider.php',
                'Providers/RouteServiceProvider.php',
                'Routes/api.php',
                'Routes/web.php',
                'module.json'
            ];
    
            foreach ($tempFilePaths as $tempFilePath) {
                if (! file_exists($tempModulePath . $tempFilePath)) {
                    File::deleteDirectory(base_path('temp/' . $zipped_file_name));
                    return [
                        'status' => 201,
                        'message' => __('The :x file does not exist.', ['x' => $tempFilePath]),
                    ];
                }
                if ($tempFilePath == 'module.json') {
                    $moduleJson = file_get_contents($tempModulePath . $tempFilePath);
                    $moduleJsonArray = !empty($moduleJson) ? json_decode($moduleJson, true) : [];
                    if (!array_key_exists('name', $moduleJsonArray) && !array_key_exists('item_id', $moduleJsonArray)) {
                        File::deleteDirectory(base_path('temp/' . $zipped_file_name));
                        return [
                            'status' => 201,
                            'message' => __('The Module file does not contain a name or an item id.'),
                        ];
                    }
                }
            }
    
            File::copyDirectory(base_path('temp'), base_path('Modules'));
            File::deleteDirectory(base_path('temp/' .  $zipped_file_name));
            
            return [
                'status' => 402,
                'addon' => Addon::findOrFail($zipped_file_name),
            ];

        } catch (Exception $e) {
            return [
                'status' => 201,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * migrateAndSedd
     *
     * @param  mixed $name
     * @return void
     */
    public static function migrateAndSeed($name)
    {
        try {
            Artisan::call('module:migrate-rollback ' . $name);
            Artisan::call('module:migrate ' . $name);
            Artisan::call('module:seed ' . $name);

            return [
                'status' => 201,
                'message' => __('Database migration and seeding are successful.'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 402,
                'message' => $e->getMessage(),
            ];
        }
    }
}
