<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace AdminTest\Controller;

use Admin\Controller\UserController;
use Admin\Form\User;
use Application\Model\Entity\User as UserEntity;
use ApplicationTest\AuthorizationTrait;
use ApplicationTest\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class UserControllerTest extends AbstractHttpControllerTestCase
{
    use AuthorizationTrait;

    /** @var UserController */
    protected $controller;
    protected $request;
    /** @var RouteMatch */
    protected $routeMatch;
    protected $event;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->setApplicationConfig(
            $serviceManager->get('ApplicationConfig')
        );
        $this->controller = new UserController($this->getApplicationServiceLocator());;
        $this->controller->setTranslator($serviceManager->get('Translator'));
        $this->routeMatch = new RouteMatch(array('controller' => 'user'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);

        parent::setUp();
    }

    public function testListActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'list');

        $this->controller->dispatch($this->getRequest());
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetMissingUser()
    {
        $this->routeMatch->setParam('id', -1);

        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){}

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    public function testAddActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'addJson');

        try{
            $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){}

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
    }

    //region Un-Authorized access tests

    public function testListActionUnAuthorized()
    {
        try{
            $this->dispatch('/admin/user/list');
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testGetUnAuthorized()
    {
        try{
            $this->dispatch('/admin/user');
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

//        $this->assertEquals(403, $this->getResponse()->getStatusCode());//in fact it returns code 302 (Redirect) !?
        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testAddPostUnAuthorized()
    {
        try{
            $this->dispatch('/admin/user', Request::METHOD_POST);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testEditPutUnAuthorized()
    {
        try{
            $this->dispatch('/admin/user/33', Request::METHOD_PUT);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testDeleteUnAuthorized()
    {
        try{
            $this->dispatch('/admin/user/33', Request::METHOD_DELETE);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    //endregion

    /**
     * Adds a user entry in the `users` DB table
     */
    public function testAddPost()
    {
        //truncate table first
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $qb = $entityManager->getRepository(get_class(new UserEntity()))->createQueryBuilder('u');
        $qb->delete()->getQuery()->execute();

        $this->getRequest()->setMethod(Request::METHOD_POST);
        $postParams = $this->prepareAddUser($this->setCurrentUser());
        $this->getRequest()->getPost()->fromArray($postParams);

        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){
            echo __FILE__.':'.__LINE__.' - Exception with message: '."\n";
            var_dump($e->getMessage());
        }
        $this->assertEquals(201, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);

        return $postParams;
    }

    /**
     * @depends testAddPost
     *
     * Test that there cannot be added another user with the same user name and email
     * @param $postParams
     */
    public function testPreventDouble($postParams)
    {
        try{
            $this->mockLogin();
            $this->dispatch('/admin/user', Request::METHOD_POST, $postParams);
        }catch(\Exception $e){
            echo __FILE__.':'.__LINE__.' - Exception with message: '."\n";
            var_dump($e->getMessage());
        }

        $this->assertEquals(200, $this->getResponse()->getStatusCode());//response code is 200 and not 201 meaning no user was created

    }

    /**
     * @depends testAddPost
     *
     * Tests if there is at least 1 entry (the newly inserted) in the list of users
     */
    public function testGetList()
    {
        $this->mockLogin();
        $this->dispatch('/admin/user');

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $contentReturned = json_decode($this->getResponse()->getContent());
        $this->assertInternalType('array', $contentReturned->lists);
        $this->assertGreaterThanOrEqual(1, count($contentReturned->lists));
    }

    /**
     * @depends testAddPost
     *
     * @param array $postParams Needed to provide the user name
     * @return mixed
     */
    public function testGetUser($postParams)
    {
        //get the user id
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $user = $entityManager->getRepository(get_class(new UserEntity()))->findOneBy(['uname' => $postParams['uname']]);

        try{
            $this->mockLogin();
            $this->dispatch('/admin/user/'.$user->getId());
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertObjectHasAttribute('form', $content);

        return $user->getId();
    }

    /**
     * @depends testGetUser
     *
     * Tests if the user can be edited successfully
     * @param int $userID Thew newly inserted user ID
     */
    public function testEditPut($userID)
    {
        $jsonModel = $this->dispatchEdit($userID, $this->setCurrentUser());

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);
    }

    /**
     * @depends testGetUser
     *
     * Test that the current user cannot see edit forms of users with greater user role
     * @param int $userID
     */
    public function testGetCannotSeeGreaterRole($userID)
    {
        //update inserted user to have role = 1 "super-admin"
        $user = $this->getInsertedUser($userID);
        $user->setRole(1);
        $this->editUser($user);

        try{
            $this->mockLogin(null, 2);//logged in with role = 2 "admin"
            $this->dispatch('/admin/user/'.$user->getId());
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertEquals(403, $this->getResponse()->getStatusCode());
    }

    /**
     * @depends testGetUser
     * @param int $userID
     */
    public function testCannotAssignGreaterRole($userID)
    {
        $jsonModel = $this->dispatchEdit($userID, $this->setCurrentUser(2), 1);

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    public function testCannotDeleteYourself()
    {
        $this->getRequest()->setMethod(Request::METHOD_DELETE);
        $this->routeMatch->setParam('id', $this->setCurrentUser()->getId());

        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    /**
     * @depends testGetUser
     * @param int $userID
     */
    public function testDeleteUser($userID)
    {
        $this->setCurrentUser();
        $this->getRequest()->setMethod(Request::METHOD_DELETE);
        $this->routeMatch->setParam('id', $userID);

        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);
    }


    /**
     * @param int $role
     * @return UserEntity
     */
    protected function setCurrentUser($role = 1)
    {
        $serviceManager = $this->controller->getServiceLocator();
        $loggedUser = $serviceManager->get('user-entity');
        $loggedUser->setId(999);
        $loggedUser->setUname('admin');
        $loggedUser->setRole($role);

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('current-user', $loggedUser);

        return $loggedUser;
    }

    /**
     * Prepares the user's post data and returns it as an array. Sets the request method to POST.
     * @param UserEntity $loggedUser
     * @param string $userRole The user role (1 - admin ... 4 - guest)
     * @param string $uname The user name to assigned
     * @return array The prepared post data.
     */
    protected function prepareAddUser($loggedUser, $userRole = '1', $uname = 'adminito')
    {
        $serviceManager = $this->controller->getServiceLocator();

        $form = new User($loggedUser, $serviceManager->get('entity-manager'));
        $password = $loggedUser->hashPassword('Demo123456');

        return [
            'uname' => $uname,
            'email' => 'ventsi@mail.com',
            'password_fields' => [
                'password' => $password,
                'password_repeat' => $password
            ],
            'role' => $userRole,
            'user_csrf' => $form->get('user_csrf')->getValue()
        ];
    }

    protected function getInsertedUser($id)
    {
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        return $entityManager->find(get_class(new UserEntity()), $id);
    }

    /**
     * @param int $editedUserID The ID of the user being edited
     * @param UserEntity $currentUser
     * @param int $newRole The new role of the edited user
     * @return mixed|null|\Zend\Stdlib\ResponseInterface
     */
    protected function dispatchEdit($editedUserID, $currentUser, $newRole = 1)
    {
        if(!is_int($newRole)) throw new \InvalidArgumentException();

        $this->getRequest()->setMethod(Request::METHOD_PUT);
        $this->routeMatch->setParam('id', $editedUserID);

        $form = new User($currentUser, $this->controller->getServiceLocator()->get('entity-manager'));

        $this->getRequest()->setContent('uname=admin3&email=ventsi2@mail.com&role='.$newRole.'&user_csrf='.$form->get('user_csrf')->getValue());
        $jsonModel = null;
        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }
        return $jsonModel;
    }


    protected function editUser($user)
    {
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $entityManager->persist($user);
        $entityManager->flush();
    }
}