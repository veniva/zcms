<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Stdlib;

class Strings
{
    public static function randomString($length = null) {
        if($length && !is_numeric($length))
            throw new \InvalidArgumentException('The supplied argument is not valid');

        $combined = array_merge(range(1, 9), range('a', 'z'), range('A', 'Z'));
        if($length < 1){
            return (string)$combined[array_rand($combined)];
        }
        else{
            $str = '';
            foreach(range(1, $length) as $letter){
                $str .= self::randomString();
            }
            return $str;
        }
    }

    public static function alias($str) {
        $str = preg_replace('/[\s]+/i', '-', str_replace(',', '', trim($str)));
        $alias = extension_loaded('mbstring') ? mb_strtolower($str) : strtolower($str);
        return $alias;
    }
}