<?php

namespace ApplicationTest\Controller;


use Application\Service\Invokable;
use ApplicationTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ApplicationTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setApplicationConfig(
            $this->serviceManager->get('ApplicationConfig')
        );
        parent::setUp();
    }

    public function testLanguages()
    {
        $this->dispatch('/');
        $language = $this->serviceManager->get('language');
        $langs = $language->getActiveLanguages();

        //test if it is array or Traversable
        if(!$langs instanceof \Traversable && !is_array($langs)){
            throw new \PHPUnit_Framework_AssertionFailedError('$langs is not of type array object');
        }
    }

    public function testServices()
    {
        $this->dispatch('/');
        $serviceManager = $this->serviceManager;

        $acl = $serviceManager->get('acl');
        $this->assertType($acl, '\Zend\Permissions\Acl\Acl');

        $auth = $serviceManager->get('auth');
        $this->assertType($auth, '\Zend\Authentication\AuthenticationService');

        $dbadapter = $serviceManager->get('dbadapter');
        $this->assertType($dbadapter, '\Zend\Db\Adapter\Adapter');

        $entityManager = $serviceManager->get('entity-manager');
        $this->assertType($entityManager, '\Doctrine\ORM\EntityManager');

        $passwordAdapter = $serviceManager->get('password-adapter');
        $this->assertType($passwordAdapter, '\Zend\Crypt\Password\Bcrypt');

        $currentUser = $serviceManager->get('current-user');
        $this->assertType($currentUser, '\Application\Model\Entity\User');
    }
    
    public function testCoreUrl()
    {
        $basePath = $this->getApplicationServiceLocator()->get('ViewRenderer')->basePath();
        $corePath = $this->getApplicationServiceLocator()->get('ViewRenderer')->corePath();

        $this->assertEquals($basePath.'/core', $corePath);
    }

    protected function assertType($obj, $type)
    {
        if(!$obj instanceof $type){
            throw new \PHPUnit_Framework_AssertionFailedError('A service object is not an instance of '.get_class($type));
        }else{
            $this->assertTrue(true);
        }
    }
}
