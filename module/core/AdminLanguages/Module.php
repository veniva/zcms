<?php
namespace AdminLanguages;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ViewModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'addLanguages'), 100);
    }

    public function addLanguages(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $viewModel = $event->getViewModel();
        $viewArray = [
            'languages' => $serviceManager->get('language'),
        ];
        $viewArray = array_merge($viewArray, $this->setRouteVariables($this->getRouteMatch($event)));
        $languagesView = new ViewModel($viewArray);
        $languagesView->setTemplate('admin-languages/layout');
        $viewModel->addChild($languagesView, 'activeAdminLanguages');
    }

    protected function setRouteVariables(RouteMatch $routeMatch)
    {
        $controller = $routeMatch->getParam('controller');
        $controller = strtolower(substr($controller, strrpos($controller, '\\')+1));
        $action = $routeMatch->getParam('action');
        $route = $routeMatch->getMatchedRouteName();

        $setArray = ['route' => $route, 'controller' => $controller, 'action' => $action];
        $alias = $routeMatch->getParam('alias');
        if($alias)
            $setArray['alias'] = $alias;

        $id = $routeMatch->getParam('id');
        if($id)
            $setArray['id'] = $id;

        $page = $routeMatch->getParam('page');
        if($page)
            $setArray['page'] = $page;

        return $setArray;
    }

    protected function getRouteMatch(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if(!$routeMatch) {
            $routeMatch = new RouteMatch(array('home'));
            $event->setRouteMatch($routeMatch);
            $routeMatch = $event->getRouteMatch();
        }
        return $routeMatch;
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
