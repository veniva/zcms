<?php

namespace Admin\Controller\Initializer;

use Zend\ServiceManager\Initializer\InitializerInterface;
use Interop\Container\ContainerInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;

class Translator implements InitializerInterface
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if($instance instanceof TranslatorAwareInterface){
            $translator = $container->get('translator');
            $instance->setTranslator($translator);
        }
    }
}