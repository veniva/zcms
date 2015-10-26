<?php

namespace Admin\Service\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CategoryTree implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        return new \Admin\CategoryTree\CategoryTree($serviceManager);
    }
}