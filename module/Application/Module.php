<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Service\Invokable\Misc;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ViewModel;
use Zend\Permissions\Acl\Exception\ExceptionInterface as AclException;

class Module
{
    private $routeMatch;

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'accessControl'));
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'setRouteMatch'), 2);
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'globalLayoutVars'), 1);

        $dbAdapter = $serviceManager->get('dbadapter');
        GlobalAdapterFeature::setStaticAdapter($dbAdapter);

        Misc::setStaticServiceLocator($serviceManager);

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $serviceManager->get('ViewHelperManager')->setFactory('langUrl', function () use ($e) {
            $viewHelper = new View\Helper\Url($e->getRouteMatch());
            return $viewHelper;
        });
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

    public function globalLayoutVars(MvcEvent $e)
    {
        if(!$e->getResponse()->contentSent()){
            $viewModel = $e->getViewModel();
            if($viewModel instanceof ViewModel){
                $routeMatch = $this->routeMatch;

                $serviceManager = $e->getApplication()->getServiceManager();
                $translator = $serviceManager->get('translator');
                $lang = $routeMatch->getParam('lang');
                $locale = ($lang != 'en') ? $lang.'_'.strtoupper($lang) : 'en_US';
                $translator->setLocale($locale);
                $serviceManager->get('ViewHelperManager')->get('translate')
                    ->setTranslator($translator);

                $controller = $routeMatch->getParam('controller');
                $controller = strtolower(substr($controller, strrpos($controller, '\\')+1));
                $action = $routeMatch->getParam('action');
                $route = $routeMatch->getMatchedRouteName();

                $setArray = ['route' => $route, 'controller' => $controller, 'action' => $action];
                $alias = $routeMatch->getParam('alias');
                if($alias)
                    $setArray['alias'] = $alias;

                $setArray['lang'] = ($lang && $lang != 'en') ? $lang : '';

                $viewModel->setVariables($setArray);
            }
        }
    }

    public function setRouteMatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if(!$routeMatch) {
            $routeMatch = new RouteMatch(array('home'));
            $e->setRouteMatch($routeMatch);
            $routeMatch = $e->getRouteMatch();
        }
        Misc::setStaticRoute($routeMatch);
        Misc::setLangID();
        $this->routeMatch = $routeMatch;
    }

    public function accessControl(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if(!$routeMatch) return;

        $serviceManager = $e->getApplication()->getServiceManager();
        $config = $serviceManager->get('config');

        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $namespace = $routeMatch->getParam('__NAMESPACE__');
        $parts = explode('\\', $namespace);
        $moduleNamespace = reset($parts);

        //if the module is not in the list of access controlled modules, grant all access
        if(!empty($config['acl']['modules']) && !in_array($moduleNamespace, $config['acl']['modules']))
            return;

        $acl = $serviceManager->get('acl');//the Acl class may be specific for each module
        $currentUser = $serviceManager->get('current-user');
        $userRole = $currentUser->getRole();

        $resourceAliases = $config['acl']['resource_aliases'];
        if(isset($resourceAliases[$controller])){
            $resource = $resourceAliases[$controller];
        }else{
            $shortControllerPosition = strrpos($controller, '\\');
            if($shortControllerPosition)
                $resource = strtolower(substr($controller, $shortControllerPosition+1));//get the short name of the controller and use it as a resource name
            else
                $resource = $controller;
        }

        //if the resource is not in the Acl then add it
        if(!$acl->hasResource($resource)){
            $acl->addResource($resource);
        }

        try{
            if($acl->isAllowed($userRole, $resource, $action)){
                return;
            }
        }catch(AclException $e){
            //v_todo - log this in the error log
        }
        $lang = $routeMatch->getParam('lang');
        $e->getResponse()->setStatusCode(403);
        $e->setRouteMatch(new RouteMatch(array('application')));
        $routeMatch = $e->getRouteMatch();
        $routeMatch->setParam('controller', 'Admin\Controller\Log');
        $routeMatch->setParam('action', 'in');
        if($lang)
            $routeMatch->setParam('lang', $lang);
    }
}
