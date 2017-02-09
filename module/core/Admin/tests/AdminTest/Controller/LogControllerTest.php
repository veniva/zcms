<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace AdminTest\Controller;


use Admin\Controller\LogController;
use Logic\Core\Admin\Form\User;
use Logic\Core\Model\Entity\CategoryContent;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\Entity\ListingContent;
use ApplicationTest\AuthorizationTrait;
use ApplicationTest\Bootstrap;
use Doctrine\ORM\EntityManager;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class LogControllerTest extends AbstractHttpControllerTestCase
{
    use AuthorizationTrait;

    /** @var LogController   */
    protected $controller;
    /** @var  EntityManager */
    protected $entityManager;
    /** @var  MvcEvent */
    protected $event;
    /** @var RouteMatch */
    protected $routeMatch;
    /** @var  Request */
    protected $request;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->setApplicationConfig(
            $serviceManager->get('ApplicationConfig')
        );
        $this->controller = new LogController($this->getApplicationServiceLocator());
        $this->controller->setTranslator($serviceManager->get('Translator'));
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'log'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);

        $this->entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        parent::setUp();
    }
    
    public function testInitialSetup()
    {
        $this->removeAllFromDB();
        
        $serviceManager = $this->controller->getServiceLocator();
        $user = $serviceManager->get('user-entity');
        $form = new User($user, $serviceManager);
        $password = $user->hashPassword('Demo123456');
        $postParams = [
            'uname' => 'admin',
            'email' => 'test@test.com',
            'password_fields' => [
                'password' => $password,
                'password_repeat' => $password
            ],
            'isoCode' => 'en',
            'language_name' => 'en',
            'user_csrf' => $form->get('user_csrf')->getValue()
        ];
        
        try{
            $this->mockLogin();
            $this->dispatch('/admin/log/initial', Request::METHOD_POST, $postParams);
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }
        
        $this->assertEquals(302, $this->getResponse()->getStatusCode());
    }

    //region Protected methods

    //region Delete DB Content
    protected function removeLanguages()
    {
        //truncate table lang
        $qb = $this->entityManager->getRepository(get_class(new Lang()))->createQueryBuilder('l');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeUsers()
    {
        //truncate table users
        $qb = $this->entityManager->getRepository(get_class(new \Logic\Core\Model\Entity\User()))->createQueryBuilder('u');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeCategoryContent()
    {
        //truncate table category_content
        $qb = $this->entityManager->getRepository(get_class(new CategoryContent()))->createQueryBuilder('co');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeListingsContent()
    {
        $qb = $this->entityManager->getRepository(get_class(new ListingContent()))->createQueryBuilder('lc');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeAllFromDB()
    {
        $this->removeCategoryContent();
        $this->removeListingsContent();
        $this->removeLanguages();
        $this->removeUsers();
    }
    //endregion
    
    //endregion
}
