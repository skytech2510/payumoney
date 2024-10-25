<?php

namespace Modules\Upgrader\Entities;

use Modules\Upgrader\Entities\Handler\{
    View, Contractor
};
use Illuminate\Support\Facades\{
    Artisan, File, Log
};
use ZipArchive;

class UpgradeManager extends Contractor
{
    /**
     * List of non-writable directories
     *
     * @var array
     */
    private $directoriesNeedPermission = [];

    /**
     * View the upgrade process
     *
     * @param string $redirectTo
     * @return void
     */
    public function view($redirectTo)
    {
        return (new View())->view($redirectTo);
    }

    /**
     * Run the upgrade process
     *
     * @return void
     */
    public function run()
    {
        $updaterJson = $this->updaterJson;
        $isValid = $this->isValid();

        if (!$isValid['status']) {
            $this->log($isValid['message']);
            return;
        }

        if(function_exists('beforeUpgrade')){
            beforeUpgrade();
        }

        $this->log(__('The system upgrading: ') . $this->getCurrentVersion() . ' to ' . $this->getLastVersion());

        try {
            if (($this->download($updaterJson['archive'])) === false) {
                $this->log(__('Enable to download the archive file. Upgrade process aborted.'));
                return;
            }

            $this->log(__('Enabling maintenance mode...'));
            Artisan::call('down');

            if ($this->install() === false) {
                $this->recovery();

                $this->log(__('Disabling maintenance mode...'));
                Artisan::call('up');
                return;
            }

            $this->setCurrentVersion($updaterJson['version']); //update system version

            $this->log(__('Disabling maintenance mode...'));
            Artisan::call('up');

            if(function_exists('afterUpgrade')){
                afterUpgrade();
            }

            $this->log('<b>' . __('The system successfully updated to ') . $this->getLastVersion() . '<b>');

        } catch (\Exception $e) {
            $this->log(__('An exception occurred: ') . '<small>' . $e->getMessage() . '</small>');
            $this->recovery();
        }
    }

    /**
     * Install the update
     *
     * @return void
     */
    private function install()
    {
        try {
            $this->copyArchiveFilesAndDirectories();
            $this->deleteFilesAndDirectories();
            $this->migrations();
            $this->seeds();
            $this->cleanUp();
            $this->refreshConfig();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Copy all the files and directories from the archive to the root directory
     *
     * @return void
     */
    private function copyArchiveFilesAndDirectories()
    {
        $this->log('<p>' . __('Copying required files') . '</p>', false);

        $copyCounter = 0;

        // Copy files
        foreach (File::allFiles(base_path('tmp')) as $file) {
            $fileRealPath = $file->getRealPath();

            if (File::exists($fileRealPath)) {
                Log::info("File Exist: " . $fileRealPath);
                $this->backup($fileRealPath, $file->getRelativePathname());
            }

            Log::info("File copied: " . $fileRealPath);

            if ($copyCounter % 5 != 0) {
                $this->log('.', false);
            }

            $copyCounter++;

            $this->copy($fileRealPath, $file->getRelativePathname());
        }
    }

    /**
     * Delete all the files and directories which are listed in the delete array
     *
     * @return void
     */
    protected function deleteFilesAndDirectories()
    {
        if (!is_array($this->updaterJson['delete'])) {
            return;
        }

        $this->log('<p>' . __('Deleting required files...') . '</p>', false);

        foreach ($this->updaterJson['delete'] as $deletableItem) {
            if (File::isFile($deletableItem) && File::exists($deletableItem)) {
                File::delete($deletableItem);
            } elseif (File::isDirectory($deletableItem) && File::exists($deletableItem)) {
                File::deleteDirectory($deletableItem);
            }
        }
    }

    /**
     * Migrate files if exist
     *
     * @return void
     */
    private function migrations()
    {
        $this->log('<p>' . __('Migrating files') . '...</p>', false);

        $version = 'v' . str_replace('.', '_', $this->getLastVersion());
        $directories = ['database', 'migrations', 'versions', $version];
        $paths = [implode('/', $directories)];

        array_unshift($directories, 'module-name');

        foreach (\Nwidart\Modules\Facades\Module::getOrdered() as $module) {
            $directories[0] = 'Modules/' . $module->getName();

            array_push($paths, implode('/', $directories));
        }

        // Create new tables if exists
        Artisan::call('migrate');

        foreach ($paths as $path) {
            if (is_dir(base_path() . '/' . $path)) {
                // Modify tables for the latest version
                Artisan::call('migrate --path=' . $path);
            }
        }
    }
    /**
     * Seed files if exist
     *
     * @return void
     */
    private function seeds()
    {
        $this->log('<p>' . __('Seeding files') . '...</p>', false);

        $version = 'v' . str_replace('.', '_', $this->getLastVersion());
        $seedDirectories = ['Database\\Seeders\\versions\\' . $version . '\\DatabaseSeeder'];

        foreach (\Nwidart\Modules\Facades\Module::getOrdered() as $module) {
            array_push($seedDirectories, 'Modules\\' . $module->getName() . '\\Database\\Seeders\\versions\\' . $version . '\\DatabaseSeeder');
        }

        foreach ($seedDirectories as $class) {
            if (class_exists($class)) {
                Artisan::call('db:seed', ['--class' => $class]);
            }
        }
    }

    /**
     * Clean up the temporary, backup and update files
     *
     * @return void
     */
    private function cleanUp()
    {
        $this->log('<p>' . __('Deleting temporary directory...') . '</p>');
        File::deleteDirectory($this->tempPath());

        $this->log('<p>' . __('Deleting backup directory...') . '</p>');
        File::deleteDirectory($this->backupPath());

        $this->log('<p>' . __('Deleting update file...') . '</p>');
        File::deleteDirectory(storage_path('updates'));
    }

    /**
     * Download the update archive file
     *
     * @param  mixed $filename
     * @return void
     */
    private function download($filename, $log = true)
    {
        if ($log) {
            $this->log(__('Downloading update from ') . $this->baseURL . '/' . $filename);
        }

        try {
            $zip = new ZipArchive;
            $res = $zip->open($this->baseURL . '/' . $filename);

            $extractToPath = $this->tempPath();

            if (is_dir($extractToPath)) {
                File::deleteDirectory($extractToPath);
            }


            if ($res === true) {
                $res = $zip->extractTo($extractToPath);
                $zip->close();
            }

        } catch (\Exception $e) {

            $this->log(__('An exception occurred: ') . $e->getMessage());

            return false;
        }

        return $this->baseURL . '/' . $filename;
    }

    /**
     * Backup the file to the backup directory
     *
     * @param  mixed $filename
     * @return void
     */
    private function backup($src, $dst)
    {
        $this->copy($src, $this->basePath() . DIRECTORY_SEPARATOR . $dst);
    }

    /**
     * Recovery the system from the backup
     *
     * @return void
     */
    private function recovery()
    {
        $this->log(__('Attempting to recovery your system from backup.'));

        try {
            $backupFiles = File::allFiles($this->backupPath());
            foreach ($backupFiles as $file) {
                $filename = $this->processRecoveryFilename((string)$file);

                File::copy($this->backupPath($filename) , $this->basePath($filename)); //to respective folder
            }
        } catch (\Exception $e) {
            $this->log(__("Recovery failed, try it manually. Run: 'php artisan up' to disable the maintenance mode."));
            $this->log(__('An exception occurred: ') . '<small>' . $e->getMessage() . '</small>');

            return false;
        }

        $this->log(__('Recovery completed successfully.'));

        return true;
    }

    /**
     * Process the recovery filename
     *
     * @param  mixed $filename
     * @return void
     */
    private function processRecoveryFilename($filename)
    {
        return substr($filename, (strlen($filename) - strlen($this->backupPath()) - 1) * (-1));
    }

    /**
     * Clear the cache, route and views
     *
     * @return void
     */
    public function refreshConfig()
    {
        try {
            $this->log(__('Clearing cache, route, views...'));
            \Artisan::call('cache:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');
        } catch (\Exception $e) {
            // something wrong here, just ignore
        }
    }

    /**
     * Check writable permission in directories
     *
     * @return bool
     */
    private function needPermission()
    {
        if (!$this->download($this->updaterJson['archive'], false)) {
            return false;
        }

        foreach (File::allFiles(base_path('tmp')) as $file) {
            $dirname = pathinfo($file->getRelativePathname())['dirname'];

            if (!File::exists($dirname) && !File::makeDirectory($dirname, 0777, true, true)) {
                $a = explode(DIRECTORY_SEPARATOR, $dirname);
                $parent = null;

                for ($i = 0; $i < sizeof($a); $i += 1) {
                    $tmppath = implode(DIRECTORY_SEPARATOR, array_slice($a, 0, $i));

                    if (empty($tmppath)) {
                        continue;
                    }

                    try {
                        if (!file_exists($tmppath)) {
                            break;
                        } else {
                            $parent = $tmppath;
                        }
                    } catch (\Exception $ex) {
                        $this->log($dirname.' not in open_basedir: ' . ini_get('open_basedir'));
                        $parent = $tmppath;
                    }
                }

                if (!is_writable($parent) && !in_array($parent, $this->directoriesNeedPermission)) {
                    $this->directoriesNeedPermission[] = $parent;
                }
            } else if (!is_writable($dirname) && !in_array($dirname, $this->directoriesNeedPermission)) {
                $this->directoriesNeedPermission[] = $dirname;
            }
        }

        return !empty($this->directoriesNeedPermission);

    }

    /**
     * Check if the update file is valid
     *
     * @return array
     */
    public function isValid()
    {
        // check if the update file is valid
        if (is_null($this->updaterJson['version']) || is_null($this->updaterJson['archive'])) {
            return [
                'status' => false,
                'message' => __('The update file is not valid.')
            ];
        }

        // check if the update version is equal to current version
        if (version_compare($this->getLastVersion(), $this->getCurrentVersion(), '=')) {
            return [
                'status' => false,
                'message' => __('The version you uploaded is the same as the current one (:x)', ['x' => $this->getLastVersion()])
            ];
        }

        // check if the update version is older than current version
        if (version_compare($this->getLastVersion(), $this->getCurrentVersion(), '<')) {
            return [
                'status' => false,
                'message' => __('The version you uploaded (:x) is older than the current one (:y)',['x' => $this->getLastVersion(), 'y' => $this->getCurrentVersion()])
            ];
        }

        // check if the update version is supported
        if (!in_array($this->getCurrentVersion(), $this->getSupportedVersions())) {
            return [
                'status' => false,
                'message' => __('You are on a version (:x) that is not supported by this update.', ['x' => $this->getCurrentVersion()])
            ];
        }

        if ($this->needPermission()) {
            return [
                'status' => false,
                'needPermission' => true,
                'permissionRequire' => $this->directoriesNeedPermission,
                'message' => __('These directories need writable permission. you need re-change the permission after successfully system update.')
            ];
        }

        // everything is ok
        return [
            'status' => true,
            'json' => $this->updaterJson,
            'message' => __('An update version (:x) of :y is available.', ['x' => $this->getLastVersion(), 'y' => env('APP_NAME', 'PayMoney')])
        ];
    }
}
