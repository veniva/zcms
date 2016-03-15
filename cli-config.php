<?php
//http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html#generating-the-database-schema
require_once 'vendor/autoload.php';
$application = \Zend\Mvc\Application::init(require __DIR__.'/config/application.config.php');
$serviceManager = $application->getServiceManager();
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($serviceManager->get('entity-manager'));