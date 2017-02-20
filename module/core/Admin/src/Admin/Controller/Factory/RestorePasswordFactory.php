<?php

namespace Admin\Controller\Factory;


use Admin\Controller\RestorePasswordController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RestorePasswordFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RestorePasswordController($serviceLocator);
    }
}