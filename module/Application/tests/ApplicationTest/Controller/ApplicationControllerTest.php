<?php

namespace ApplicationTest\Controller;


use Application\Service\Invokable;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ApplicationControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        parent::setUp();
    }

    public function testLanguages()
    {
        $this->dispatch('/');
        $langs = Invokable\Misc::getActiveLangs();

        //test if it is array or Traversable
        if(!$langs instanceof \Traversable && !is_array($langs)){
            throw new \PHPUnit_Framework_AssertionFailedError('$langs is not of type array object');
        }
    }

    public function testCategories()
    {
        $this->dispatch('/');
        $categories = Invokable\Layout::getTopCategories();
        $this->assertInternalType('array', $categories);

        foreach($categories as $category){
            $this->assertInternalType('array', $category['listings']);
        }
    }

    public function testBreadcrumb()
    {
        $this->dispatch('/');
        $this->assertEmpty(Invokable\Layout::breadcrumb());

        $this->dispatch('/category');
        $this->assertInternalType('array', Invokable\Layout::breadcrumb());
    }

    public function testInvokableServices()
    {
        $this->dispatch('/');
        $serviceManager = Invokable\Misc::getStaticServiceLocator();

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

    protected function assertType($obj, $type)
    {
        if(!$obj instanceof $type){
            throw new \PHPUnit_Framework_AssertionFailedError('A service object is not an instance of '.get_class($type));
        }else{
            $this->assertTrue(true);
        }
    }
}
