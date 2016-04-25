<?php
return array(
    'Zend\Loader\StandardAutoloader' => array(
        'namespaces' => array(
            $namespace => dirname(__DIR__ ). '/src/' . $namespace,
        ),
    ),
    //uncomment the line below in order to use newly generated class map (note: you have to generate it first)
    /*'Zend\Loader\ClassMapAutoloader' => array(
        dirname(__DIR__).'/autoload_classmap.php'
    ),*/
);