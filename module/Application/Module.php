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
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Validator\AbstractValidator;
use Zend\View\Model\ViewModel;
use Application\Model\Entity\Lang;

class Module
{
    /**
     * @var \Zend\Mvc\Router\RouteMatch
     */
    private $routeMatch;

    public function init(ModuleManager $moduleManager)
    {
        if(extension_loaded('mbstring'))
            mb_internal_encoding("UTF-8");
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'setRouteMatch'), -1);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'accessControl'), -2);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'globalLayoutVars'), -3);
        //use the error template of the currently used module
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function(MvcEvent $event)use($serviceManager) {
            if($route = $event->getRouteMatch()){
                if($event->getRouteMatch()->getParam('controller') == 'Admin\Controller\Log'){//show simple layout with this particular controller
                    $event->getViewModel()->setTemplate('layout/blank');
                }else{
                    $moduleName = strtolower(strstr($route->getParam('__NAMESPACE__'), '\\', true));
                    $layoutName = $moduleName == 'application' ? 'layout' : $moduleName;
                    if(isset($serviceManager->get('config')['view_manager']['template_map'][$layoutName.'/layout'])){
                        $event->getViewModel()->setTemplate($layoutName.'/layout');
                    }
                }
            }else{//show blank error page
                $event->getViewModel()->setTemplate('layout/blank');
            }

        }, -200);

        $dbAdapter = $serviceManager->get('dbadapter');
        GlobalAdapterFeature::setStaticAdapter($dbAdapter);

        Misc::setStaticServiceLocator($serviceManager);
        AbstractValidator::setDefaultTranslator(new \Zend\Mvc\I18n\Translator($serviceManager->get('translator')));

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

    /**
     * @deprecated - v_todo schedule for removal
     * Defines some layout variables that can be easily used i a layout.phtml
     * This is an alternative to Misc::getStaticRoute()->getParam('some-param')
     * @param MvcEvent $e
     */
    public function globalLayoutVars(MvcEvent $e)
    {
        if(!$e->getResponse()->contentSent()){
            $viewModel = $e->getViewModel();
            if($viewModel instanceof ViewModel){
                $routeMatch = $this->routeMatch;

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

                $viewModel->setVariables($setArray);
            }
        }
    }

    //@deprecated v_todo - schedule for removal
    public function setRouteMatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if(!$routeMatch) {
            $routeMatch = new RouteMatch(array('home'));
            $e->setRouteMatch($routeMatch);
            $routeMatch = $e->getRouteMatch();
        }
        Misc::setStaticRoute($routeMatch);
        $this->routeMatch = $routeMatch;
        $this->setLanguages($e, $this->routeMatch);
    }

    public function accessControl(MvcEvent $e)
    {
        $routeMatch = $this->routeMatch;
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
        $userRole = $currentUser->getRoleName();

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

//        try{
            if($acl->isAllowed($userRole, $resource, $action)){
                return;
            }
//        }
//        catch(AclException $ex){
//            //v_todo - log this in the error log
//        }
        $lang = $routeMatch->getParam('lang');
        $e->getResponse()->setStatusCode(403);
        $e->setRouteMatch(new RouteMatch(array('admin/default')));
        $routeMatch = $e->getRouteMatch();
        $routeMatch->setParam('controller', 'Admin\Controller\Log');//v_todo - redirect to Action prohibited location instead
        $routeMatch->setParam('action', 'in');
        if($lang)
            $routeMatch->setParam('lang', $lang);
    }

    /**
     * @deprecated v_todo - schedule for removal
     * @param MvcEvent $e
     * @param RouteMatch $routeMatch
     */
    public function setLanguages(MvcEvent $e, RouteMatch $routeMatch)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        //set current language
        $matchedLangIso = $routeMatch->getParam('lang', null);//first, coming from parameter; if none - coming from default language; if none then null

        //set the translator's locale - the "locale" is the name of the translation files located in "languages"
        $translator = $serviceManager->get('translator');
        $locale = ($matchedLangIso != 'en') ? $matchedLangIso.'_'.strtoupper($matchedLangIso) : 'en_US';
        $translator->setLocale($locale);
        $serviceManager->get('ViewHelperManager')->get('translate')->setTranslator($translator);
    }
}
