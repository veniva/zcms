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

    public function __invoke($name, $params = [], $options = [])
    {
        $view = $this->getView();
        if($this->routeMatch){
            $lang = $this->routeMatch->getParam('lang');
            if($lang)
                $params['lang'] = $lang;
        }

        return $view->url($name, $params, $options);
    }
}
