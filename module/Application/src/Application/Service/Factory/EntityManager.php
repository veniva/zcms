<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 04/08/2015
 * Time: 13:29
 */

namespace Application\Service\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager as DocEManager;

class EntityManager implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Doctrine\ORM\EntityManager
     * @throws \Doctrine\ORM\ORMException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        //set the Doctrine configuration array
        $doctrineDbSettings = (array)$config['db'];
        $doctrineDbSettings['driver'] = strtolower($config['db']['driver']);
        $doctrineDbSettings['dbname'] = $config['db']['database'];
        $doctrineDbSettings['host'] = $config['db']['hostname'];
        $doctrineDbSettings['user'] = $config['db']['username'];

        $doctrineConfig = Setup::createAnnotationMetadataConfiguration($config['doctrine']['entity_path']);
        $doctrineConfig->setAutoGenerateProxyClasses(true);
        $doctrineEntityManager = DocEManager::create($doctrineDbSettings, $doctrineConfig);

        return $doctrineEntityManager;
    }
}