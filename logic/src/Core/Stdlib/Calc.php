<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Stdlib;

class Calc
{
    public static function bytesToKilobytes($bytes)
    {
        return $bytes / 1024;
    }

    public static function bytesToMegabytes($bytes)
    {
        return $bytes / 1024 / 1024;
    }

    public static function kilobytesToBytes($kb)
    {
        return $kb * 1024;
    }

    public static function kilobytesToMegabytes($kb)
    {
        return $kb / 1024;
    }

    public static function megabytesToBytes($mb)
    {
        return $mb * 1024 * 1024;
    }

    public static function megabytesToKilobytes($mb)
    {
        return $mb * 1024;
    }
}