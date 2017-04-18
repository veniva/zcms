<?php

namespace Admin\Controller\Factory;

use Admin\Controller\RestorePasswordController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RestorePasswordFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RestorePasswordController($container);
    }
}