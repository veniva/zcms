<?php

namespace Application\Service\Initializer;


use Logic\Core\Model\PasswordAwareInterface;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Password implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if($instance instanceof PasswordAwareInterface){
            $instance->setPasswordAdapter($serviceLocator->get('password-adapter'));
        }
    }
}