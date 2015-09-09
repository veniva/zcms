<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 08/09/2015
 * Time: 11:18
 */

namespace Application\View\Helper;
use Zend\View\Helper;

class Url extends Helper\AbstractHelper
{
    public function __construct($routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    public function __invoke($name, $options = [])
    {
        $view = $this->getView();
        $lang = $this->routeMatch->getParam('lang');
        if($lang)
            $options['lang'] = $lang;
        return $view->url($name, $options);
    }
}