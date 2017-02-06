<?php

namespace Admin\Controller\Factory;


use Admin\Controller\ResetPasswordController;
use Logic\Core\Admin\Authenticate\ResetPassword;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Logic\Core\Adapters\Zend\Http\Request;

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
        $em = $serviceLocator->getServicelocator()->get('entity-manager');
        $request = $serviceLocator->getServicelocator()->get('Request');
        return new ResetPasswordController($serviceLocator, new ResetPassword(new Request($request), $em));
    }
}