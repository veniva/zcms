<?php
spl_autoload_register(function($className){
    $arr = explode('\\', $className);
    if($arr[0] == 'Logic')//only include classes from this namespace
        include $className.'.php';
});