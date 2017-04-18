<?php

namespace Application\Service\Factory;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class DbAdapter implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        return new Adapter($config['db']);
    }
}
