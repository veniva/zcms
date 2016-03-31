<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace AdminTest\Controller;

ini_set('xdebug.var_display_max_data', -1);

use Admin\Form\Listing as ListingForm;
use Application\Model\Entity;
use Application\Model\Entity\Category;
use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use Application\Model\Entity\Listing;
use Application\Model\Entity\ListingContent;
use Application\Stdlib\Strings;
use ApplicationTest\AuthorizationTrait;
use ApplicationTest\Bootstrap;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Admin\Controller\ListingController;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\View\Model\JsonModel;

class ListingControllerTest extends AbstractHttpControllerTestCase
{
    use AuthorizationTrait;
    /** @var ListingController */
    protected $controller;
    protected $request;
    /** @var RouteMatch */
    protected $routeMatch;
    protected $event;
    /** @var  EntityManager */
    protected $entityManager;
    protected $listingEntityClassName;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->setApplicationConfig(
            $serviceManager->get('ApplicationConfig')
        );
        $this->controller = new ListingController($this->getApplicationServiceLocator());
        $this->controller->setTranslator($serviceManager->get('Translator'));
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'listing'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);

        $this->entityManager = $this->controller->getServiceLocator()->get('entity-manager');
        $this->listingEntityClassName = get_class(new Listing());


        parent::setUp();
    }

    public function testListActionCanBeAccessed()
    {
        $this->removeAllFromDB();

        $this->routeMatch->setParam('action', 'list');

        $this->dispatchRequest();
        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
    }

    public function testAddActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'addJson');

        $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
    }

    //region Un-Authorized access tests

    public function testListActionUnAuthorized()
    {
        try{
            $this->dispatch('/admin/listing/list');
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testGetUnAuthorized()
    {
        try{
            $this->dispatch('/admin/listing');
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
            $this->dispatch('/admin/listing', Request::METHOD_POST);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testEditPutUnAuthorized()
    {
        try{
            $this->dispatch('/admin/listing/33', Request::METHOD_PUT);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    public function testDeleteUnAuthorized()
    {
        try{
            $this->dispatch('/admin/listing/33', Request::METHOD_DELETE);
        }catch(\Exception $e){
            var_dump($e->getMessage());
        }

        $this->assertActionName('in');
        $this->assertControllerName('Admin\Controller\Log');
    }

    //endregion

    public function testCannotAddNoCategory()
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $form = new ListingForm($this->entityManager);
        $this->getRequest()->getPost()->fromArray([
            'sort' => 0,
            'content' => [],
            'metadata' => [],
            'category' => null,
            'listing_csrf' => $form->get('listing_csrf')->getValue()
        ]);

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);


    }

    public function testAddListing()
    {
        $this->fillRequiredData();
        $categoryTree = $this->controller->getServiceLocator()->get('category-tree');
        $categoryTree->reSetCategories();//re-fill in the category select form element options to prevent notInArray validation error

        $this->getRequest()->setMethod(Request::METHOD_POST);
        //get the first category available in the table
        $categories = $this->entityManager->getRepository(get_class(new Category()))->findAll();
        $categoryId = reset($categories)->getId();
        $postData = $this->prepareAddPostData($categoryId);
        $this->getRequest()->getPost()->fromArray($postData);

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(201, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);

        return $categoryId;
    }

    public function testGetListing()
    {
        //get the inserted listing
        $qb = $this->entityManager->getRepository($this->listingEntityClassName)->createQueryBuilder('l');
        $qb->select('l.id')->join('l.content', 'lc')->where("lc.title='\"Title in en'");
        $id = $qb->getQuery()->getSingleScalarResult();

        try{
            $this->mockLogin();
            $this->dispatch('/admin/listing/'.$id);
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertEquals(false, strpos($content->form, '<exotico>'));//assert StripTags
        $this->assertGreaterThanOrEqual(0, strpos($content->form, '&quot;Title'));//assert htmlentities
        $this->assertGreaterThanOrEqual(0, strpos($content->form, '&quot;Link'));//assert htmlentities

        return $id;
    }

    /**
     * @depends testGetListing
     * @depends testAddListing
     * @param int $id
     * @param int $categID
     * @return string The new title in English to be checked in the next test
     */
    public function testUpdate($id, $categID)
    {
        $this->routeMatch->setParam('id', $id);
        $this->getRequest()->setMethod(Request::METHOD_PUT);

        $params = $this->prepareEditData($categID);
        $params['content'][0]['title'] = $newTitle = "New title in English";

        $this->dispatchExternal('/admin/listing/'.$id, Request::METHOD_PUT, $params);

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertEquals('success', $content->message->type);

        return $newTitle;
    }

    /**
     * Test that the previous update was really successful
     * @depends testGetListing
     * @depends testUpdate
     * @param int $id
     * @param string $newTitle
     */
    public function testUpdatedSuccessfully($id, $newTitle)
    {
        $this->dispatchExternal('/admin/listing/'.$id);

        $content = json_decode($this->getResponse()->getContent());
        $this->assertNotFalse(strpos(html_entity_decode($content->form), $newTitle));
    }

    public function testUpdateWrongID()
    {
        $this->routeMatch->setParam('id', 999);
        $this->getRequest()->setMethod(Request::METHOD_PUT);

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    /**
     * @depends testGetListing
     * @depends testAddListing
     * Test that the previous update was really successful
     * @param int $id
     * @param int $categID
     */
    public function testUpdateMissingLink($id, $categID)
    {
        $params = $this->prepareEditData($categID);
        $params['content'][0]['link'] = '';
        $params['content'][1]['link'] = '';

        $this->dispatchExternal('/admin/listing/'.$id, Request::METHOD_PUT, $params);

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertEquals('error', $content->message->type);
    }

    /**
     * @depends testGetListing
     * @depends testAddListing
     * Test that the previous update was really successful
     * @param int $id
     * @param int $categID
     */
    public function testUploadImage($id, $categID)
    {
        //deal with image
        $params = $this->prepareEditWithImage($categID, 'ellipse.png');

        $this->dispatchExternal('/admin/listing/'.$id, Request::METHOD_PUT, $params);

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertEquals('success', $content->message->type);
    }

    /**
     * @depends testGetListing
     * @depends testAddListing
     * Test that the previous update was really successful
     * @param int $id
     * @param int $categID
     */
    public function testEditImageTooBig($id, $categID)
    {
        $params = $this->prepareEditWithImage($categID, 'ellipse-big.png');

        $this->dispatchExternal('/admin/listing/'.$id, Request::METHOD_PUT, $params);

        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $content = json_decode($this->getResponse()->getContent());
        $this->assertEquals('error', $content->message->type);
    }

    public function testDeleteListingInvalidId()
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->routeMatch->setParam('action', 'deleteAjax');
        $this->getRequest()->getPost()->fromArray([
            'ids' => '999'
        ]);

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('error', $jsonModel->getVariable('message')['type']);
    }

    /**
     * @depends testGetListing
     *
     * @param int $id
     */
    public function testDeleteListing($id)
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->routeMatch->setParam('action', 'deleteAjax');
        $this->getRequest()->getPost()->fromArray([
            'ids' => $id
        ]);

        $jsonModel = $this->dispatchRequest();

        $this->assertEquals(200, $this->controller->getResponse()->getStatusCode());
        $this->assertEquals('success', $jsonModel->getVariable('message')['type']);
    }

    //region Protected methods

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

    protected function dispatchExternal($url, $method = null, array $params = [])
    {
        try{
            $this->mockLogin();
            $this->dispatch($url, $method, $params);
        }catch(\Exception $e){
            echo "\n".__FILE__.':'.__LINE__.' Message: '.$e->getMessage();
        }
    }

    protected function prepareAddPostData($categoryID)
    {
        $form = new ListingForm($this->entityManager);
        $postParams = [
            'sort' => 0,
            'metadata' => [],
            'category' => $categoryID,
            'listing_csrf' => $form->get('listing_csrf')->getValue()
        ];

        $allLangs = $this->entityManager->getRepository(get_class(new Lang()))->findAll();
        foreach($allLangs as $lang){
            $title = '<exotico>"Title in '.$lang->getIsoCode();
            $postParams['content'][] = [
                'link' => '<exotico>"Link in '.$lang->getIsoCode(),
                'title' => $title,
                'alias' => Strings::alias($title),
                'text' => 'Text in '.$lang->getIsoCode(),
            ];
        }

        return $postParams;
    }

    protected function prepareEditData($categID)
    {
        $form = new ListingForm($this->entityManager);
        return [
            'sort' => 1,
            'category' => $categID,
            'content' => [
                ['title' => 'Some new title en', 'link' => 'New link en', 'alias' => 'new alias in en', 'text' => 'New text in en'],
                ['title' => 'New title in Es', 'link' => 'New link es', 'alias' => 'new alias in es', 'text' => 'New text in es']
            ],
            'listing_csrf' => $form->get('listing_csrf')->getValue()
        ];
    }

    protected function prepareEditWithImage($categID, $imgName)
    {
        $config = $this->controller->getServiceLocator()->get('config');
        $path = $config['public-path'].'img/'.$imgName;
        $pathInfo = pathinfo($path);
        $data = file_get_contents($path);

        $params = $this->prepareEditData($categID);
        $params['listing_image']['name'] = $pathInfo['basename'];
        $params['listing_image']['base64'] = base64_encode($data);

        return $params;
    }

    //region Add DB Data
    protected function addLanguages()
    {
        foreach([['iso' => 'en', 'name' => 'English'], ['iso' => 'es', 'name' => 'Spanish']] as $language){
            $lang = new Lang();
            $lang->setIsoCode($language['iso']);
            $lang->setName($language['name']);
            $lang->setStatus(1);
            $this->entityManager->persist($lang);
        }
    }

    protected function addCategory()
    {
        $category = new Category();
        $this->entityManager->persist($category);
    }

    protected function fillRequiredData()
    {
        $this->addLanguages();
        $this->addCategory();
        $this->entityManager->flush();
    }
    //endregion

    //region Delete DB Content
    protected function removeCategoryContent()
    {
        //truncate table category_content
        $qb = $this->entityManager->getRepository(get_class(new CategoryContent()))->createQueryBuilder('co');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeCategories()
    {
        $qb = $this->entityManager->getRepository(get_class(new Category()))->createQueryBuilder('c');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeLanguages()
    {
        //truncate table lang
        $qb = $this->entityManager->getRepository(get_class(new Lang()))->createQueryBuilder('l');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeListingsContent()
    {
        $qb = $this->entityManager->getRepository(get_class(new ListingContent()))->createQueryBuilder('lc');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeListingsMetadata()
    {
        $qb = $this->entityManager->getRepository(get_class(new Entity\Metadata()))->createQueryBuilder('mt');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeListingImages()
    {
        $qb = $this->entityManager->getRepository(get_class(new Entity\ListingImage()))->createQueryBuilder('l_img');
        $qb->delete()->getQuery()->execute();
        //delete all from directory
        $config = $this->controller->getServiceLocator()->get('config');
        $listingImagesPath = $path = $config['public-path'].'img/listing_img';
        $fileSystem = new Filesystem\Filesystem();
        $fileSystem->rename($listingImagesPath.'/.gitignore', dirname($listingImagesPath).'/.gitignore');//get the gitignore file out of the dir to be removed
        try{
            $fileSystem->remove($listingImagesPath);//remove the non empty directory
        }catch(Filesystem\Exception\IOExceptionInterface $e){
            var_dump($e->getMessage());
        }
        //create the listing image dir again
        mkdir($listingImagesPath);
        $fileSystem->rename(dirname($listingImagesPath).'/.gitignore', $listingImagesPath.'/.gitignore');
    }

    protected function removeListings()
    {
        $qb = $this->entityManager->getRepository(get_class(new Listing()))->createQueryBuilder('li');
        $qb->delete()->getQuery()->execute();
    }

    protected function removeAllFromDB()
    {
        $this->removeCategoryContent();
        $this->removeCategories();
        $this->removeListingsContent();
        $this->removeListingsMetadata();
        $this->removeListingImages();
        $this->removeListings();
        $this->removeLanguages();
    }
    //endregion

    //endregion
}