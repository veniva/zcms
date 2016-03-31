<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
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
