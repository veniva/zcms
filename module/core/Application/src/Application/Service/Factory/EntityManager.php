<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Service\Factory;

use Doctrine\ORM\Events;
use Doctrine\ORM\Query\ResultSetMapping;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager as DocEManager;
use Doctrine\Common\EventArgs;

class EntityManager implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        //set the Doctrine configuration array
        $doctrineDbSettings = (array)$config['db'];
        $doctrineDbSettings['driver'] = strtolower($config['db']['driver']);
        $doctrineDbSettings['dbname'] = isset($config['db']['database']) ? $config['db']['database'] : null;
        $doctrineDbSettings['host'] = isset($config['db']['hostname']) ? $config['db']['hostname'] : null;
        $doctrineDbSettings['user'] = isset($config['db']['username']) ? $config['db']['username'] : null;

        $proxyPath = isset($config['doctrine']['proxy_dir']) ? $config['doctrine']['proxy_dir'] : null;
        $isDevMode = isset($config['doctrine']['is_dev_mode']) ? $config['doctrine']['is_dev_mode'] : false;

        $doctrineConfig = Setup::createAnnotationMetadataConfiguration($config['doctrine']['entity_path'], $isDevMode, $proxyPath);
        $doctrineConfig->setAutoGenerateProxyClasses(true);
        $doctrineEntityManager = DocEManager::create($doctrineDbSettings, $doctrineConfig);

        if(isset($config['doctrine']['initializers'])) {
            $eventManager = $doctrineEntityManager->getEventManager();

            foreach ($config['doctrine']['initializers'] as $initializer) {
                $eventClass = new DoctrineEvent(new $initializer(), $container);
                $eventManager->addEventListener(Events::postLoad, $eventClass);
            }
        }

        if($doctrineDbSettings['driver'] == 'pdo_sqlite'){//it is very important to make sure foreign keys are on with SQLite
            $query = $doctrineEntityManager->createNativeQuery("pragma foreign_keys=1", new ResultSetMapping());
            $query->execute();
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
        $obj = $this->initializer;
        $obj($this->serviceLocator, $entity);
    }
}
