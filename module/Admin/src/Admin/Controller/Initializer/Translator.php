<?php

namespace Admin\Controller\Initializer;


use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;

class Translator implements InitializerInterface
{

    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $controllerManager
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $controllerManager)
    {
        if($instance instanceof TranslatorAwareInterface){
            $translator = $controllerManager->getServiceLocator()->get('translator');
            $instance->setTranslator($translator);
        }
    }
}