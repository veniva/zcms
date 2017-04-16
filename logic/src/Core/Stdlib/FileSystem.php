<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Stdlib;

/**
 * Class FileSystem - contains useful functions for dealing with the file system
 * @package Application\Stdlib
 */
class FileSystem
{
    const SUCCESS = 0;
    const ERR_CREATE_DIR = 10;
    const ERR_SAVE_FILE = 11;
    const ERR_WRONG_FILE_TYPE = 12;
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
        closedir($handle);
        return  true;
    }

    public static function saveBase64File($base64String, $dir, $name, &$size = 0, &$extension = '')
    {
        if(!is_dir($dir)){
            if(!mkdir($dir)){
                return self::ERR_CREATE_DIR;
            }
        }

        if(file_put_contents($dir.'/'.$name, $base64String)=== false){
            return self::ERR_SAVE_FILE;
        }

        return self::SUCCESS;
    }

    public static function extractBase64FromBrowserImageUpload($base64String, &$size = 0, &$extension = '')
    {
        if(empty($base64String)) throw new \InvalidArgumentException('Argument $base64String may not be empty');

        $matches = array();
        preg_match('/data:image\/(png|jpeg|gif|jpg);base64,/', $base64String, $matches);

        if(!empty($matches[0])){
            $image = preg_replace('%'.$matches[0].'%', '', $base64String);
            $size = ((strlen($image) * 3) / 4) / 1024;//base 64 encoded is about 33% bigger then the original
            $extension = $matches[1];
            return base64_decode($image);
        }else{
            return self::ERR_WRONG_FILE_TYPE;
        }
    }

    public static function getFileExtension($fileName)
    {
        return substr(strrchr($fileName, '.'), 1);
    }
}