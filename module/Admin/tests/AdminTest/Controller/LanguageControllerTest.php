<?php

namespace AdminTest\Controller;

use ApplicationTest\Bootstrap;
use Zend\I18n\Exception\ExtensionNotLoadedException;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Admin\Controller\ListingController;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class LanguageControllerTest extends AbstractHttpControllerTestCase
{
    /** @var ListingController */
    protected $controller;
    protected $request;
    /** @var RouteMatch */
    protected $routeMatch;
    protected $event;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new ListingController();
        $this->controller->setTranslator(new \Zend\i18n\Translator\Translator());
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'language'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);

        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        parent::setUp();
    }

    public function testListActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'index');

        try{
            $this->controller->dispatch($this->request);
        }catch(\Exception $e){}
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEditActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'edit');
        $this->routeMatch->setParam('id', 1);//requires an actual listing ID v_todo - refactor this

        try{
            $this->controller->dispatch($this->request);
        }catch(\Exception $e){}
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAddActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'add');

        try{
            $this->controller->dispatch($this->request);
        }catch(\Exception $e){}
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

}