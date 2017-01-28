<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace AdminTest\Controller;

use Admin\Form\Language;
use Logic\Core\Model\Entity\Lang;
use ApplicationTest\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Admin\Controller\LanguageController;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class LanguageControllerTest extends AbstractHttpControllerTestCase
{
    use \ApplicationTest\AuthorizationTrait;

    /** @var LanguageController */
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
        $this->controller = new LanguageController($this->getApplicationServiceLocator());
        $this->controller->setTranslator($serviceManager->get('translator'));
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'language'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);

        parent::setUp();
    }

    public function testListActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'list');

        try{
            $this->controller->dispatch($this->request);
        }catch(\Exception $e){}
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetMissingLanguage()
    {
        $this->routeMatch->setParam('id', -1);

        try{
            $jsonModel = $this->controller->dispatch($this->request);
        }catch(\Exception $e){}
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    public function testAddActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'addJson');

        try{
            $this->controller->dispatch($this->request);
        }catch(\Exception $e){}
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    //region Un-Authorized access tests

    public function testListActionUnAuthorized()
    {
        try{
            $this->dispatch('/admin/language/list');
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testGetUnAuthorized()
    {
        try{
            $this->dispatch('/admin/language');
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
            $this->dispatch('/admin/language', Request::METHOD_POST);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testEditPutUnAuthorized()
    {

        try{
            $this->dispatch('/admin/language/33', Request::METHOD_PUT);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testDeleteUnAuthorized()
    {

        try{
            $this->dispatch('/admin/language/33', Request::METHOD_DELETE);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    //endregion

    /**
     * Adds a default language entry in the `lang` DB table
     */
    public function testAddPost()
    {
        //truncate table first
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $languages = $entityManager->getRepository(get_class(new Lang()))->findAll();
        foreach($languages as $language){
            $entityManager->remove($language);
        }
        $entityManager->flush();

        $serviceManager = $this->controller->getServiceLocator();
        $form = new Language($serviceManager);
        $this->request->setMethod(Request::METHOD_POST);
        $postParams = [
            'isoCode' => 'en',
            'name' => 'English99',
            'status' => '2',
            'language_csrf' => $form->get('language_csrf')->getValue()
        ];
        $this->request->getPost()->fromArray($postParams);

        try{
            $this->controller->dispatch($this->request);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }
        $this->assertEquals(201, $this->controller->getResponse()->getStatusCode());

        return $postParams;
    }

    /**
     * @depends testAddPost
     *
     * Test if the list of languages is fetched properly, expecting at least one language that's already inserted via testAddPost()
     */
    public function testGetList()
    {
        try{
            $this->mockLogin();
            $this->dispatch('/admin/language');
        }catch(\Exception $e){
            var_dump($e->getMessage());
            exit;
        }
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $contentReturned = json_decode($this->getResponse()->getContent());
        $this->assertInternalType('array', $contentReturned->lists);
        $this->assertGreaterThanOrEqual(1, count($contentReturned->lists));
    }

    /**
     * @depends testAddPost
     * @param array $postParams Parameters containing the name of the newly inserted language
     */
    public function testGetLanguage($postParams)
    {
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $insertedLanguage = $entityManager->getRepository(get_class(new Lang()))->findOneBy(array('name' => $postParams['name']));

        try{
            $this->mockLogin();
            $this->dispatch('/admin/language/'.$insertedLanguage->getId());
        }catch(\Exception $e){
            var_dump($e->getMessage());
            exit;
        }

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $contentReturned = json_decode($this->getResponse()->getContent());
        $this->assertObjectHasAttribute('form', $contentReturned);

        return $insertedLanguage->getId();
    }

    /**
     * @depends testGetLanguage
     * @param int $id
     */
    public function testEditPut($id)
    {
        $this->request->setMethod(Request::METHOD_PUT);
        $this->routeMatch->setParam('id', $id);

        $form = new Language($this->controller->getServiceLocator());
        $this->request->setContent('isoCode=es&name=Spanish100&status=2&language_csrf='.$form->get('language_csrf')->getValue());
        try{
            $jsonModel = $this->controller->dispatch($this->request);
        }catch(\Exception $e){
            var_dump($e->getMessage());
            exit;
        }

        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);
    }

    /**
     * @depends testGetLanguage
     * @param int $id
     */
    public function testEditMissingTitle($id)
    {
        $form = new Language($this->controller->getServiceLocator());

        try{
            $this->mockLogin();
            $this->dispatch('/admin/language/'.$id, Request::METHOD_PUT, ['isoCode' => 'au', 'name' => '', 'language_csrf' => $form->get('language_csrf')->getValue()]);
        }catch(\Exception $e){
            var_dump(__FILE__.':'.__LINE__.' Message: '.$e->getMessage());
        }
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertObjectHasAttribute('form', $content);
    }

    /**
     * @depends testGetLanguage
     * @param int $id
     */
    public function testCannotDeleteDefaultLanguage($id)
    {
        $this->routeMatch->setParam('id', $id);
        $this->request->setMethod(Request::METHOD_DELETE);

        //test cannot delete default language (with status=2)
        try{
            $jsonModel = $this->controller->dispatch($this->request);
        }catch(\Exception $e){
            var_dump($e->getMessage());
            exit;
        }
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);

    }

    /**
     * @depends testGetLanguage
     * @var int $id
     */
    public function testDeleteLanguage($id)
    {
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $insertedLanguage = $this->getInsertedLanguage($id);
        //change the status to '1' manually
        $insertedLanguage->setStatus(1);
        $entityManager->persist($insertedLanguage);
        $entityManager->flush();

        //test can delete active language (with status=1)
        $this->request->setMethod(Request::METHOD_DELETE);
        $this->routeMatch->setParam('id', $id);
        try{
            $jsonModel = $this->controller->dispatch($this->request);
        }catch(\Exception $e){
            var_dump($e->getMessage());
            exit;
        }
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);
    }

    protected function getInsertedLanguage($id)
    {
        $entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        return $entityManager->find(get_class(new Lang()), $id);
    }

}