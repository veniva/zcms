<?php
//http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html#generating-the-database-schema
require_once __DIR__.'/Bootstrap.php';
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet(\ApplicationTest\Bootstrap::getServiceManager()->get('entity-manager'));