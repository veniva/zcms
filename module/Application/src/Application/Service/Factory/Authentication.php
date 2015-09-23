<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 15/09/2015
 * Time: 17:10
 */

namespace Application\Service\Factory;


use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Authentication implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authentication = new AuthenticationService();
        $adapter = $serviceLocator->get('auth-adapter');
        $authentication->setAdapter($adapter);

        return $authentication;
    }
}
