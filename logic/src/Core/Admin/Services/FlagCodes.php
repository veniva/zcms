<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Admin\Services;

class FlagCodes
{
    protected $flagsDir;

    public function __construct(string $publicPath = '')
    {
        $this->flagsDir = $publicPath.'img/flags';
    }

    public function getFlagCodeOptions()
    {
        $codes = [];

        if(is_dir($this->flagsDir)){
            if($dh = opendir($this->flagsDir)){
                while(($file = readdir($dh)) !== false){
                    if($file != '.' && $file != '..'){
                        if($code = $this->extractIsoCode($file))
                            $codes[$code] = $code;
                    }
                }
            }
        }
        return $codes;
    }

    protected function extractIsoCode($fileName)
    {
        $name = strstr($fileName, '.', true);
        if(strlen($name) == 2){
            return $name;
        }
        return null;
    }
}