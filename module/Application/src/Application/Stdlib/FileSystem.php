<?php

namespace Application\Stdlib;

/**
 * Class FileSystem - contains useful functions for dealing with the file system
 * @package Application\Stdlib
 */
class FileSystem
{
    /**
     * Check if the directory is empty; useful to check before rmdir is used
     * @param $dir
     * @return bool|null
     */
    public function isDirEmpty($dir)
    {
        if (!is_readable($dir))
            return null;

        $handle = opendir($dir);
        while (($entry = readdir($handle)) !== false) {
            if ($entry != "." && $entry != "..") {
                return  false;
            }
        }
        return  true;
    }
}