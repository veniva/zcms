<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 15/02/2016
 * Time: 16:05
 */

namespace Application\Stdlib;


class Strings
{
    public function randomString($length = null) {
        if($length && !is_numeric($length))
            throw new \InvalidArgumentException('The supplied argument is not valid');

        $combined = array_merge(range(1, 9), range('a', 'z'), range('A', 'Z'));
        if($length < 1){
            return (string)$combined[array_rand($combined)];
        }
        else{
            $str = '';
            foreach(range(1, $length) as $letter){
                $str .= $this->randomString();
            }
            return $str;
        }
    }
}