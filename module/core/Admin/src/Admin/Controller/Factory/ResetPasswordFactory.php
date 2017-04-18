<?php

namespace Admin\Controller\Factory;

use Admin\Controller\ResetPasswordController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ResetPasswordFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ResetPasswordController($container);
    }
}