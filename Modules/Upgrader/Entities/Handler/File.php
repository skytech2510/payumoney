<?php

namespace Modules\Upgrader\Entities\Handler;

use Illuminate\Support\Facades\File as BaseFile;

class File
{
    /**
     * Copy a file or directory to another location
     *
     * @param string $src
     * @param string $dst
     * @return void
     */
    public static function copy($src, $dst)
    {
        if (BaseFile::exists($dst)) {
            // Delete the file or link or directory
            if (is_link($dst) || is_file($dst)) {
                BaseFile::delete($dst);
            } else {
                BaseFile::deleteDirectory($dst);
            }
        } else {
            // Make sure the PARENT directory exists
            $dirname = pathinfo($dst)['dirname'];

            if (!BaseFile::exists($dirname)) {
                BaseFile::makeDirectory($dirname, 0777, true, true);
            }
        }

        // if source is a file, just copy it
        if (BaseFile::isFile($src)) {
            BaseFile::copy($src, $dst);
        } else {
            BaseFile::copyDirectory($src, $dst);
        }
    }
}