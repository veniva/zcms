<?php

namespace Application\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class ValidatorMessages implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return  new \Application\Validator\ValidatorMessages($serviceLocator->get('translator'));
    }
}