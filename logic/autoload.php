<?php
spl_autoload_register(function($className){
    $arr = explode('\\', $className);
    if($arr[0] == 'Logic'){//only include classes from this namespace
        $path = __DIR__;

        for($i = 1; $i < count($arr); $i++){
            $path .= DIRECTORY_SEPARATOR.$arr[$i];
        }

        include $path.'.php';
    }
});