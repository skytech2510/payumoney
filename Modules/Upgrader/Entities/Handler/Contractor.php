<?php

namespace Modules\Upgrader\Entities\Handler;

use Illuminate\Support\Facades\Log;
use Modules\Upgrader\Entities\Handler\Json;
use Illuminate\Support\Facades\File;
use App\libraries\Env;
use Modules\Upgrader\Entities\Handler\File as UpgraderFile;

class Contractor
{
    /**
     * The updater json array
     *
     * @var array
     */
    protected $updaterJson = [];

    /**
     * The base url
     *
     * @var string
     */
    protected $baseURL;

    /**
     * The base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * The backup path
     *
     * @var string
     */
    protected $backupPath;

    /**
     * The temp path
     *
     * @var string
     */
    protected $tmpFolderName = 'tmp';
    
    /**
     * The temp path
     *
     * @var string
     */
    protected $tempPath;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setUpdaterJson();
        $this->setBaseURL();
        $this->setBasePath();
        $this->setBackupDirectory();
        $this->setTempPath();
        $this->sheild();
    }
    
    /**
     * Set the updater json array from the json file
     *
     * @return void
     */
    protected function setUpdaterJson()
    {
        return $this->updaterJson = (new Json)->get();
    }
    
    /**
     * Set the base url
     *
     * @return void
     */
    protected function setBaseURL()
    {
        $this->baseURL = storage_path() . "/updates";
    }
    
    /**
     * Set the base path of the application
     *
     * @return void
     */
    protected function setBasePath()
    {
        $this->basePath = base_path();
    }
    
    /**
     * Get the base path of the application
     *
     * @param  mixed $path
     * @return string
     */
    protected function basePath($path = null)
    {
        if (is_null($path)) {
            return $this->basePath;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }
    
    /**
     * Set the temp path to store the downloaded archive
     *
     * @return void
     */
    protected function setTempPath()
    {
        $tempPath = $this->basePath($this->tmpFolderName);
        
        if (!is_dir($tempPath)) {
            File::makeDirectory($tempPath, 0755, true, true);
        }

        $this->tempPath = $tempPath;
    }
    
    /**
     * Get the temp path
     *
     * @param  mixed $path
     * @return string
     */
    protected function tempPath($path = null)
    {
        if (is_null($path)) {
            return $this->tempPath;
        }

        return $this->tempPath . DIRECTORY_SEPARATOR . $path;
    }
    
    /**
     * Set the backup path to store the backup files
     *
     * @return void
     */
    protected function setBackupDirectory()
    {
        $backupDir = $this->basePath('backup_' . date('Ymd'));
        
        if (!is_dir($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $this->backupPath = $backupDir;
    }
    
    /**
     * Get the backup path
     *
     * @param  mixed $path
     * @return string
     */
    protected function backupPath($path = null)
    {
        if (is_null($path)) {
            return $this->backupPath;
        }

        return $this->backupPath . DIRECTORY_SEPARATOR . $path;
    }
    
    /**
     * Log the message to show in the browser and log file to the laravel log
     *
     * @param  mixed $message
     * @param  mixed $break
     * @return void
     */
    public function log($message, $break = true)
    {
        if ($break) {
            echo "<p>" . $message . "</p>";
            Log::info("Upgrader - " . $message);
        } else {
            echo $message;
            $this->obEndFlushAll();
            flush();
        }
    }

    /**
     * Copy the file from source to destination
     *
     * @param  mixed $src
     * @param  mixed $dst
     * @return void
     */
    public function copy($src, $dst)
    {
        UpgraderFile::copy($src, $dst);
    }

    /**
     * End of all output buffers
     *
     * @return void
     */
    private function obEndFlushAll() {
        $levels = ob_get_level();
        for ( $i = 0; $i < $levels; $i++ ) {
            ob_end_flush();
        }
    }

    /**
     * Include the upgrade file if exists
     *
     * @return void
     */
    private function sheild()
    {
        $upgradeFile = storage_path('updates/upgrade.php');
        
        if (!file_exists($upgradeFile)) {
            return;
        }

        include_once $upgradeFile;
    }
        
    /**
     * Get the current version of the application
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return config('paymoney.version');
    }
        
    /**
     * Set the current version of the application
     *
     * @param  mixed $version
     * @return void
     */
    protected function setCurrentVersion($version)
    {
        changeEnvironmentVariable('APP_VERSION', $version);
    }
    
    /**
     * Get the last version of the application
     *
     * @return string
     */
    public function getLastVersion()
    {
        return $this->updaterJson['version'];
    }
    
    /**
     * Get the supported versions of the application to upgrade
     *
     * @return array
     */
    public function getSupportedVersions()
    {
        return $this->updaterJson['supported_versions'];
    }
}