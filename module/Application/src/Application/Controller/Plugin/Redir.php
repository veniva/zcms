<?php

namespace Application\Controller\Plugin;


use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\Exception;

class Redir extends Redirect
{
    public function toRoute($route = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        $controller = $this->getController();
        $paramsPlugin = $controller->plugin('params');
        $lang = $paramsPlugin->fromRoute('lang');
        if($lang)
            $params['lang'] = $lang;

        return parent::toRoute($route, $params, $options, $reuseMatchedParams);
    }
}
