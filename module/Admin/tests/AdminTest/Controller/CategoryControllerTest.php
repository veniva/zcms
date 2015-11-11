<?php

namespace AdminTest\Controller;


use ApplicationTest\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Admin\Controller\CategoryController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;

class CategoryControllerTest extends PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $request;
    protected $response;
    /** @var RouteMatch */
    protected $routeMatch;
    protected $event;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new CategoryController(new \Zend\i18n\Translator\Translator());
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'category'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'index');

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEditAndAddActionsCanBeAccessed()
    {
        //test edit
        $this->routeMatch->setParam('action', 'edit');
        $this->routeMatch->setParam('id', 111);//requires an actual category ID v_todo - refactor this

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        //test add
        $this->routeMatch->setParam('action', 'add');
        $this->routeMatch->setParam('id', 0);

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}