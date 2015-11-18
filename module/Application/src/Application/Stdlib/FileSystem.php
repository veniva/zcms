<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

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