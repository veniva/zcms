<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
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
