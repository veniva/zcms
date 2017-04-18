<?php

namespace Application\Service\Factory;

use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PasswordAdapter implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $options = isset($config['bcrypt']) ? $config['bcrypt'] : array();
        $adapter =  new Bcrypt($options);
        return $adapter;
    }
}
