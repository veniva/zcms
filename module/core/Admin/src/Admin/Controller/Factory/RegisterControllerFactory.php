<?php

namespace Admin\Controller\Factory;

use Admin\Controller\RegisterController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RegisterControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RegisterController($container);
    }
}