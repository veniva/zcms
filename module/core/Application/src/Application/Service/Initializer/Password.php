<?php

namespace Application\Service\Initializer;

use Logic\Core\Model\PasswordAwareInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;
use Interop\Container\ContainerInterface;

class Password implements InitializerInterface
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if($instance instanceof PasswordAwareInterface){
            $instance->setPasswordAdapter($container->get('password-adapter'));
        }
    }
}