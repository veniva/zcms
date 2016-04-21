<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace AdminTest\Controller;

use Admin\Form\Category as CategoryForm;
use Admin\View\Helper\Breadcrumb;
use Application\Model\Entity\Category;
use Application\Model\Entity\Lang;
use ApplicationTest\AuthorizationTrait;
use ApplicationTest\Bootstrap;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Admin\Controller;
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
    /** @var Controller\CategoryController */
    protected $controller;
    protected $response;
    /** @var RouteMatch */
    protected $routeMatch;
    protected $event;
    /** @var EntityManager */
    protected $entityManager;
    protected $categoryClassName;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->setApplicationConfig(
            $serviceManager->get('ApplicationConfig')
        );
        $controllerFactory = new Controller\Factory\CategoryControllerFactory();
        $this->controller = $controllerFactory->createService($this->getApplicationServiceLocator());
        $this->controller->setTranslator($serviceManager->get('Translator'));
        $this->routeMatch = new RouteMatch(array('controller' => 'category'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);

        $this->entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $this->categoryClassName = get_class(new Category);

        parent::setUp();
    }

    /**
     * @group basicCategory
     */
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
        $postParams = $this->preparePostAddData();
        $this->getRequest()->getPost()->fromArray($postParams);
        $this->getRequest()->setMethod(Request::METHOD_POST);

        try{
            $jsonModel = $this->controller->dispatch($this->getRequest());
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage()."\n";
        }
        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    /**
     * @group basicCategory
     */
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
     * @group basicCategory
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
     * @group basicCategory
     * @depends testGetCategory
     * @param int $id
     * @return int
     */
    public function testAddChildCategories($id)
    {
        //add child category
        $postParams = $this->preparePostAddData($id, 'ch');
        $this->getRequest()->getPost()->fromArray($postParams);
        $this->getRequest()->setMethod(Request::METHOD_POST);

        $this->dispatchRequest();
        $this->assertEquals(201, $this->controller->getResponse()->getStatusCode());

        return $id;
    }

    /**
     * @group basicCategory
     * @depends testAddChildCategories
     * @param int $id
     * @return int The new child category ID
     */
    public function testGetChildrenCategories($id)
    {
        //get the child categories
        $category = $this->entityManager->find($this->categoryClassName, $id);
        $children = $this->entityManager->getRepository($this->categoryClassName)->getChildren($category);
        $this->assertInternalType('array', $children);
        $this->assertInstanceOf(get_class(new Category()), $children[0]);

        $this->assertEquals($id, $children[0]->getParent());
        return $children[0];
    }

    /**
     * @depends testGetChildrenCategories
     *
     * @param Category $childCategory
     */
    public function testAddGrandChild($childCategory)
    {
        //add child to the child category
        $this->mockLogin();
        $postParams = $this->preparePostAddData($childCategory->getId(), 'ch2');
        $this->dispatch('/admin/category/', Request::METHOD_POST, $postParams);
        $this->assertEquals(201, $this->getResponse()->getStatusCode());
        $grandChild = $this->entityManager->getRepository($this->categoryClassName)->getChildren($childCategory)[0];
        $this->assertEquals($childCategory->getId(), $grandChild->getParent());

        return $grandChild;
    }

    public function testAddCategoryPost2()
    {
        //add one more root category
        $this->mockLogin();
        $postParams = $this->preparePostAddData(null, 'b');
        $this->dispatch('/admin/category/', Request::METHOD_POST, $postParams);
        $this->assertEquals(201, $this->getResponse()->getStatusCode());
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select($qb->expr()->max('c.id'))->from(get_class(new Category), 'c');
        $lastInsertedId = $qb->getQuery()->getSingleScalarResult();

        return $lastInsertedId;
    }

    /**
     * @depends testGetChildrenCategories
     * @depends testAddCategoryPost2
     * @param Category $childCategory
     * @param $lastInsertedId
     */
    public function testChangeChild($childCategory, $lastInsertedId)
    {
        $childId = $childCategory->getId();
        $categoryClassName = get_class(new Category);
        $this->mockLogin();
        $form = new CategoryForm($this->entityManager);
        $this->dispatch('/admin/category/'.$childId, Request::METHOD_PUT, [
            'sort' => 5,
            'content[0][title]' => urlencode('Some title'),
            'parent' => $lastInsertedId,
            'category_csrf' => $form->get('category_csrf')->getValue(),
        ]);
        $this->assertEquals(200, $this->getResponse()->getStatusCode());

        //check if the child category has changed it's direct parent
        $childCategory = $this->entityManager->find($categoryClassName, $childId);
        $this->assertEquals($lastInsertedId, $childCategory->getParent());

        //check all the parents of the child category
        $parentIDs = [];
        foreach($childCategory->getParents() as $parent){
            $parentIDs[] = $parent->getId();
        }
        $this->assertTrue(in_array($lastInsertedId, $parentIDs));

        //check the child's child parents
        $childChild = $this->entityManager->getRepository($categoryClassName)->getChildren($childCategory)[0];
        $childParentIDs = [];
        foreach($childChild->getParents() as $descendants){
            $childParentIDs[] = $descendants->getId();
        }
        $this->assertTrue(in_array($lastInsertedId, $childParentIDs));
        $this->assertEquals($childCategory->getId(), $childChild->getParent());//test the direct parent
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
        $this->getRequest()->setContent('id=0&sort=1&content[0][title]='.urlencode($newTitle).'&parent='.'&category_csrf='.$form->get('category_csrf')->getValue());

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
            'parent' => ''
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
            'parent' => ''
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
     * @depends testAddGrandChild
     * @param $greatChild
     */
    public function testBreadcrumb($greatChild)
    {
        $defaultLanguage = $this->entityManager->getRepository(get_class(new Lang()))->findOneBy(['status' => 2]);

        $breadcrumb = new Breadcrumb($greatChild->getContent()[0], $defaultLanguage);
        $bc = $breadcrumb->build();
        
        $this->assertInternalType('array', $bc);
        $this->assertEquals(3, count($bc));
    }

    /**
     * Tests that the Breadcrumb will contain the containing <Category name>"
     * @depends testGetCategory
     * @depends testEditCategoryPut
     * @param int $id
     * @param string $categTitle The category title to look for in the breadcrumb
     */
    public function testGetListAndBreadcrumb($id, $categTitle)
    {
        try{
            $this->mockLogin();
            $this->dispatch('/admin/category', null, ['parent' => $id]);
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertInternalType('array', $content->lists);
        $this->assertRegExp("/$categTitle/", $content->breadcrumb);//test the breadcrumb
    }

    public function testDeleteCategoryWrongID()
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
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine();
        }
        return $jsonModel;
    }

    /**
     * @param null $parentId
     * @param string $uniqueToken This should be changed with every successful insertion to prevent validator errors
     * @return array
     */
    protected function preparePostAddData($parentId = null, $uniqueToken = 'a')
    {
        $form = new CategoryForm($this->entityManager);
        $postParams = [
            'parent' => $parentId,
            'sort' => 0,
            'category_csrf' => $form->get('category_csrf')->getValue(),
        ];
        $allLangs = $this->entityManager->getRepository(get_class(new Lang()))->findAll();
        foreach($allLangs as $lang){
            $postParams['content'][] = ['title' => 'Title in '.$lang->getIsoCode().$uniqueToken];
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

    protected function removeLanguage()
    {
        //truncate table lang
        $qb = $this->entityManager->getRepository(get_class(new Lang()))->createQueryBuilder('l');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeCategories()
    {
        $categories = $this->entityManager->getRepository(get_class(new Category()))->findAll();
        foreach($categories as $category){
            $this->entityManager->remove($category);
        }
        $this->entityManager->flush();
    }

    protected function removeAllFromDB()
    {
        $this->removeCategories();
        $this->removeLanguage();
    }
    //endregion
    //endregion
}