<?php

namespace Admin\Controller\Factory;


use Admin\Controller\ResetPasswordController;
use Logic\Core\Admin\Authenticate\ResetPassword;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResetPasswordFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ResetPasswordController($serviceLocator, new ResetPassword());
    }
}