<?php

namespace Admin\Controller\Factory;

use Admin\Controller\ListingController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ListingControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ListingController($container);
    }
}