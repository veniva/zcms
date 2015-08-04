<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 11:43
 */

namespace Application\Service\Factory;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DbAdapter implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        return new Adapter($config['db']);
    }
}