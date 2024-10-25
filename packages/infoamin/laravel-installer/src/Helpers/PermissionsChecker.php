<?php

namespace Infoamin\Installer\Helpers;

class PermissionsChecker
{
    /**
     * @var array
     */
    protected $datas = [];

    /**
     * Set the data array permissions and errors.
     *
     * @return mixed
     */
    public function __construct()
    {
        $this->datas['permissions'] = [];
        $this->datas['errors']      = null;
    }

    /**
     * Check for the folders permissions.
     *
     * @param array $folders
     * @return array
     */
    public function checkPermission(array $folders)
    {
        foreach($folders as $folder => $permission){
            if(!($this->getPermission($folder) >= $permission)){
                $this->setFileAndSetErrors($folder, $permission, false);
            } else {
                $this->setFile($folder, $permission, true);
            }
        }

        return $this->datas;
    }

    /**
     * Get a folder permission.
     *
     * @param $folder
     * @return string
     */
    private function getPermission($folder)
    {
        return mb_substr(sprintf('%o', fileperms(base_path($folder))), -4);
    }

    /**
     * Add the file to the list of results.
     *
     * @param $folder
     * @param $permission
     * @param $isActive
     */
    private function setFile($folder, $permission, $isActive)
    {
        array_push($this->datas['permissions'], [
            'folder' => $folder,
            'permission' => $permission,
            'isActive' => $isActive
        ]);
    }

    /**
     * Add the file and set the errors.
     *
     * @param $folder
     * @param $permission
     * @param $isActive
     */
    private function setFileAndSetErrors($folder, $permission, $isActive)
    {
        $this->setFile($folder, $permission, $isActive);
        $this->datas['errors'] = true;
    }
}