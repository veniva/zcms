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
use Doctrine\Common\EventArgs;

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

        if(isset($config['doctrine']['initializers'])) {
            $eventManager = $doctrineEntityManager->getEventManager();

            foreach ($config['doctrine']['initializers'] as $initializer) {
                $eventClass = new DoctrineEvent(new $initializer(), $serviceLocator);
                $eventManager->addEventListener(\Doctrine\ORM\Events::postLoad, $eventClass);
            }
        }

        return $doctrineEntityManager;
    }
}

class DoctrineEvent
{
    protected $initializer;

    public function __construct($initializer, $serviceLocator)
    {
        $this->initializer = $initializer;
        $this->serviceLocator = $serviceLocator;
    }
    public function postLoad(EventArgs $event)
    {
        $entity = $event->getEntity();
        $this->initializer->initialize($entity, $this->serviceLocator);
    }
}
