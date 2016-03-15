<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace AdminTest\Controller;

use Admin\Form\Category as CategoryForm;
use Application\Model\Entity\Category;
use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use ApplicationTest\AuthorizationTrait;
use ApplicationTest\Bootstrap;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Admin\Controller\CategoryController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Stdlib\Parameters;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\View\Model\JsonModel;

class CategoryControllerTest extends AbstractHttpControllerTestCase
{
    use AuthorizationTrait;
    /** @var CategoryController */
    protected $controller;
    protected $response;
    /** @var RouteMatch */
    protected $routeMatch;
    protected $event;
    /** @var EntityManager */
    protected $entityManager;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new CategoryController();
        $this->controller->setTranslator($serviceManager->get('Translator'));
        $this->routeMatch = new RouteMatch(array('controller' => 'category'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);

        $this->setApplicationConfig(
            $serviceManager->get('ApplicationConfig')
        );

        $this->controller->setServiceLocator($this->getApplicationServiceLocator());
        $this->entityManager = $this->controller->getServiceLocator()->get('entity-manager');

        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        //remove redundant content
        $this->removeAllFromDB();

        $this->routeMatch->setParam('action', 'list');

        $this->controller->dispatch($this->getRequest());
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAddActionCanBeAccessed()
    {
        //test add
        $this->routeMatch->setParam('action', 'addJson');
        $this->getRequest()->setQuery(new Parameters(['id' => 0]));

        try{
            $this->controller->dispatch($this->getRequest());
        }catch(ServiceNotFoundException $ex){}
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    //region Un-Authorized access tests

    public function testListActionUnAuthorized()
    {
        try{
            $this->dispatch('/admin/category/list');
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testGetUnAuthorized()
    {
        try{
            $this->dispatch('/admin/category');
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
            $this->dispatch('/admin/category', Request::METHOD_POST);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testEditPutUnAuthorized()
    {
        try{
            $this->dispatch('/admin/category/33', Request::METHOD_PUT);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testDeleteUnAuthorized()
    {
        try{
            $this->dispatch('/admin/category/33', Request::METHOD_DELETE);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    //endregion

    /**
     * Test that there cannot be added category if there is no exiting languages
     */
    public function testCannotAddNoLanguage()
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $form = new CategoryForm($this->entityManager);
        $this->getRequest()->getPost()->fromArray([
            'id' => 0,
            'content' => [],
            'sort' => 0,
            'category_csrf' => $form->get('category_csrf')->getValue(),
        ]);

        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage()."\n";
        }
        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    public function testAddCategoryPost()
    {
        //add languages
        $this->addLanguages();

        $postParams = $this->preparePostAddData();
        $this->getRequest()->getPost()->fromArray($postParams);
        $this->getRequest()->setMethod(Request::METHOD_POST);

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(201, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);

        return $postParams;
    }

    /**
     * @depends testAddCategoryPost
     * @param array $postParams
     */
    public function testGetCategory($postParams)
    {
        $insertedCategory = $this->entityManager->getRepository(get_class(new Category()))->findOneBy(['sort' => $postParams['sort']]);

        try{
            $this->mockLogin();
            $this->dispatch('/admin/category/'.$insertedCategory->getId());
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertObjectHasAttribute('form', $content);

        return $insertedCategory->getId();
    }

    /**
     * @depends testGetCategory
     * @param int $id
     * @return string The new title as string
     */
    public function testEditCategoryPut($id)
    {
        $this->getRequest()->setMethod(Request::METHOD_PUT);
        $this->routeMatch->setParam('id', $id);

        $form = new CategoryForm($this->entityManager);
        $newTitle = 'Edited title';
        $this->getRequest()->setContent('id=0&sort=1&content[0][title]='.urlencode($newTitle).'&category_csrf='.$form->get('category_csrf')->getValue());

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);

        return $newTitle;
    }

    /**
     * Sends Update PUT request with an empty title
     * @depends testGetCategory
     * @param int $id
     */
    public function testEditNoValidTitle($id)
    {
        $this->getRequest()->setMethod(Request::METHOD_PUT);

        $this->dispatchInvalidUpdate($id, [
            'id' => 0,
            'sort' => 1,
            'content' => [
                ['title' => ''] //set empty title
            ],
        ]);

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertObjectHasAttribute('form', $content);
    }

    /**
     * Sends Update PUT request with an missing sort
     * @depends testGetCategory
     * @param int $id
     */
    public function testEditMissingSort($id)
    {
        $this->getRequest()->setMethod(Request::METHOD_PUT);

        $this->dispatchInvalidUpdate($id, [
            'id' => 0,
            'content' => [
                ['title' => 'Some title'] //set empty title
            ],
        ]);

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertObjectHasAttribute('form', $content);
    }

    /**
     * Sends Update PUT request with wrong edited category ID
     * @depends testGetCategory
     * @param int $id
     */
    public function testEditWrongID($id)
    {
        $this->getRequest()->setMethod(Request::METHOD_PUT);
        $this->routeMatch->setParam('id', 999); //set wrong ID

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    /**
     * Tests that the Breadcrumb will contain the containing <Category name>"
     * @depends testGetCategory
     * @depends testEditCategoryPut
     * @param int $id
     * @param string $categTitle The category title to look for in the breadcrumb
     */
    public function testGetList($id, $categTitle)
    {
        try{
            $this->mockLogin();
            $this->dispatch('/admin/category', null, ['parent_id' => $id]);
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertInternalType('array', $content->lists);
        $this->assertRegExp("/$categTitle/", $content->breadcrumb);//test the breadcrumb
    }

    /**
     * @depends testGetCategory
     * @param int $id
     */
    public function testDeleteCategoryWrongID($id)
    {
        $this->getRequest()->setMethod(Request::METHOD_DELETE);
        $this->routeMatch->setParam('id', 999);//set wrong ID

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    /**
     * @depends testGetCategory
     * @param int $id
     */
    public function testDeleteCategory($id)
    {
        $this->getRequest()->setMethod(Request::METHOD_DELETE);
        $this->routeMatch->setParam('id', $id);

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);
    }

    //region Protected Methods
    protected function dispatchInvalidUpdate($id, array $putParams)
    {
        $form = new CategoryForm($this->entityManager);
        $putParams['category_csrf'] = $form->get('category_csrf')->getValue();
        try{
            $this->mockLogin();
            $this->dispatch('/admin/category/'.$id, Request::METHOD_PUT, $putParams);
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }
    }

    protected function dispatchRequest()
    {
        $jsonModel = new JsonModel();
        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }
        return $jsonModel;
    }

    protected function preparePostAddData()
    {
        $form = new CategoryForm($this->entityManager);
        $postParams = [
            'id' => 0,
            'sort' => 0,
            'category_csrf' => $form->get('category_csrf')->getValue(),
        ];
        $allLangs = $this->entityManager->getRepository(get_class(new Lang()))->findAll();
        foreach($allLangs as $lang){
            $postParams['content'][] = ['title' => 'Title in '.$lang->getIsoCode()];
        }

        return $postParams;
    }

    protected function addLanguages()
    {
        $langs = [
            ['iso' => 'en', 'name' => 'English'],
            ['iso' => 'es', 'name' => 'Spanish'],
        ];
        foreach($langs as $language){
            $lang = new Lang();
            $lang->setIsoCode($language['iso']);
            $lang->setName($language['name']);
            if($language['iso'] == 'en')
                $lang->setStatus(2);
            else
                $lang->setStatus(1);

            $this->entityManager->persist($lang);
        }

        $this->entityManager->flush();
    }

    //region Delete DB Content
    protected function removeCategoryContent()
    {
        //truncate table category_content
        $qb = $this->entityManager->getRepository(get_class(new CategoryContent()))->createQueryBuilder('co');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeLanguage()
    {
        //truncate table lang
        $qb = $this->entityManager->getRepository(get_class(new Lang()))->createQueryBuilder('l');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeCategory()
    {
        $qb = $this->entityManager->getRepository(get_class(new Category()))->createQueryBuilder('c');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeAllFromDB()
    {
        $this->removeCategoryContent();
        $this->removeCategory();
        $this->removeLanguage();
    }
    //endregion
    //endregion
}