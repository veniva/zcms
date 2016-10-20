<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\EventManager\EventManager;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Validator\AbstractValidator;
use Zend\View\Model\ViewModel;

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
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'setLanguages'), -2);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'accessControl'), -3);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'globalLayoutVars'), -4);
        $eventManager->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'bootstrapSession'), 100);

        //use the error template of the currently used module
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'controllerNotFound'), -200);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'actionNotFound'), -201);

        $eventManager->getSharedManager()->attach('custom', '403', function(MvcEvent $event) use($eventManager){
            $viewModel = new ViewModel();
            $viewModel->setTemplate('error/403');
            $appViewModel = $event->getViewModel();
            $appViewModel->setTemplate('layout/layout');
            $appViewModel->addChild($viewModel, 'content');
            $eventManager->attach(MvcEvent::EVENT_DISPATCH, function(MvcEvent $event) {
                $event->stopPropagation(true);
            }, 100);
        });

        $dbAdapter = $serviceManager->get('dbadapter');
        GlobalAdapterFeature::setStaticAdapter($dbAdapter);

        AbstractValidator::setDefaultTranslator(new \Zend\Mvc\I18n\Translator($serviceManager->get('translator')));

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $serviceManager->get('ViewHelperManager')->setFactory('langUrl', function () use ($e) {
            $viewHelper = new View\Helper\Url($e->getRouteMatch());
            return $viewHelper;
        });

        if($serviceManager->has('text-cache')){
            // This will be used to check if there is already cached page and return it.
            // The priority must be low in order to be executed after the routing is done
            $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this,'getPageCache'), -1000);
            // And this will be used to save a generated cache page.
            // The priority must be low in order to be executed after the rendering is done
            $eventManager->attach(MvcEvent::EVENT_RENDER, array($this,'savePageCache'), -10000);

            $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this,'getActionCache'), 2);
            $eventManager->attach(MvcEvent::EVENT_RENDER, array($this,'saveActionCache'), 0);
        }
    }

    /**
     * Set the layout for cases where controller is not matched
     * @param MvcEvent $event
     */
    public function controllerNotFound(MvcEvent $event)
    {
        if($route = $event->getRouteMatch()){
            if($event->getRouteMatch()->getParam('controller') == 'Admin\Controller\Log'){//show simple layout with this particular controller
                $event->getViewModel()->setTemplate('layout/blank');
            }else{
                $this->setLayoutTemplate($event);
            }
        }else{//show blank error page
            $event->getViewModel()->setTemplate('layout/blank');
        }
        $this->setModule404Template($event);
    }

    /**
     * Set the layout used when controller is found but the action is not found
     * @param MvcEvent $event
     */
    public function actionNotFound(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        if($controller && $action == 'not-found'){
            $this->setLayoutTemplate($event);
            //set the "content" child template to be {module_name}/404
            $this->setModule404Template($event);
        }
    }

    /**
     * Set Module layout template to {module_name}/layout
     * @param MvcEvent $event
     * @return bool
     */
    private function setLayoutTemplate(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if(!$routeMatch) return false;

        $serviceManager = $event->getApplication()->getServiceManager();

        $moduleName = $this->getModuleName($routeMatch);
        $layoutName = $moduleName == 'application' ? 'layout' : $moduleName;
        if(isset($serviceManager->get('config')['view_manager']['template_map'][$layoutName.'/layout'])){
            $event->getViewModel()->setTemplate($layoutName.'/layout');
            return true;
        }
        return false;
    }

    /**
     * Extract the Module name from the namespace
     * @param RouteMatch $routeMatch
     * @return string
     */
    private function getModuleName(RouteMatch $routeMatch)
    {
        return strtolower(strstr($routeMatch->getParam('__NAMESPACE__'), '\\', true));
    }

    /**
     * Either sets the {module_name}/404 template, or leave the error/404 template
     * @param MvcEvent $event
     * @return bool
     */
    private function setModule404Template(MvcEvent $event)
    {
        if(!$event->getRouteMatch())
            return false;

        $serviceManager = $event->getApplication()->getServiceManager();
        $contentView = new ViewModel();
        $moduleName = $this->getModuleName($event->getRouteMatch());
        $template = $moduleName.'/404';
        if(isset($serviceManager->get('config')['view_manager']['template_map'][$template])){
            $contentView->setTemplate($template);
            $event->getViewModel()->addChild($contentView, 'content');
            return true;
        }
        return false;
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        $namespace = __NAMESPACE__;
        return include __DIR__.'/config/module.autoloader.config.php';
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

    public function setRouteMatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if(!$routeMatch) {
            $routeMatch = new RouteMatch(array('home'));
            $e->setRouteMatch($routeMatch);
            $routeMatch = $e->getRouteMatch();
        }
        $this->routeMatch = $routeMatch;
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

        if($acl->isAllowed($userRole, $resource, $action)){
            return;
        }

        $lang = $routeMatch->getParam('lang');
        $e->getResponse()->setStatusCode(403);

        //by our custom convention, it is prepended by the module name
        $login = isset($config['acl'][strtolower($moduleNamespace).'_login']) ? $config['acl'][strtolower($moduleNamespace).'_login'] : null;
        if(!isset($login['route'])){
            $eventManager = new EventManager('custom');
            $eventManager->trigger('403', $e);
            return;
        }

        //set login route
        $newRoute = new RouteMatch(array($login['route']));
        $e->setRouteMatch($newRoute);

        //set login controller
        if(isset($login['controller']))
            $newRoute->setParam('controller', $login['controller']);

        //set login action
        if(isset($login['action']))
            $newRoute->setParam('action', $login['action']);

        if($lang)
            $newRoute->setParam('lang', $lang);
    }

    /**
     * @param MvcEvent $e
     */
    public function setLanguages(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $languageService = $serviceManager->get('language');

        $defaultLanguage = $languageService->getCurrentLanguage();
        $languageIso = $defaultLanguage->getIsoCode();

        //set the translator's locale - the "locale" is the name of the translation files located in "languages"
        $locale = ($languageIso != 'en') ? $languageIso.'_'.strtoupper($languageIso) : 'en_US';
        $translator = $serviceManager->get('translator');
        $translator->setLocale($locale);
        $serviceManager->get('ViewHelperManager')->get('translate')->setTranslator($translator);
    }

    public function bootstrapSession(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $sessionConfig = new SessionConfig();
        $config = $serviceManager->get('Config');
        $sessionConfig->setOptions($config['session']);
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);

        //prevent session fixation by generating new session id every 5 min
        $session = new Container();//uses the "Default" namespace
        if(!isset($session->generated) || $session->generated < (time() - 300)){
            $sessionManager->regenerateId();
            $session->generated = time();
        }
    }

    //region Code courtesy of https://github.com/slaff/learnzf2

    public function getPageCache(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if(!$match) {
            return false;
        }

        if($match->getParam('page_cache')) {
            // the page can be cached so lets check if we have a cache copy of it
            $cache = $event->getApplication()->getServiceManager()->get('text-cache');
            $cacheKey = $this->pageCacheKey($match);
            $data = $cache->getItem($cacheKey);
            if(null !== $data) {
                $response = $event->getResponse();
                $response->setContent($data);

                // When we return a response object we actually shortcut the execution and the action responsible
                // for this page is not be executed
                return $response;
            }
        }
        return false;
    }

    public function savePageCache(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if(!$match) {
            return;
        }

        if($match->getParam('page_cache')) {
            $response = $event->getResponse();
            $data = $response->getContent();
            $cache = $event->getApplication()->getServiceManager()->get('text-cache');
            $cacheKey = $this->pageCacheKey($match);
            $cache->setItem($cacheKey, $data);
            $tags = $match->getParam('tags');
            if (is_array($tags)) {
                $cache->setTags($cacheKey, $tags);
            }
        }
    }

    // Action cache implementation
    public function getActionCache(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if(!$match) {
            return;
        }

        if($match->getParam('action_cache')) {
            $cache = $event->getApplication()->getServiceManager()->get('text-cache');
            $cacheKey = $this->actionCacheKey($match);
            $data = $cache->getItem($cacheKey);
            if(null !== $data) {
                // When data comes from the cache
                // we don't want the saveActionCache method to refresh this cache
                $match->setParam('action_cache',false);

                $viewModel = $event->getViewModel();
                $viewModel->setVariable($viewModel->captureTo(), $data);
                $event->stopPropagation(true);
                return $viewModel;
            }
        }
    }

    public function saveActionCache(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if(!$match) {
            return;
        }

        if($match->getParam('action_cache')) {
            $viewManager = $event->getApplication()->getServiceManager()->get('viewmanager');

            $result    = $event->getResult();
            if($result instanceof ViewModel) {
                $cache = $event->getApplication()->getServiceManager()->get('text-cache');
                // Warning: The line below needs improvement. It will work for all PHP templates, but have
                //		    to be made more flexible if you plan to use other template systems.
                $renderer = $event->getApplication()->getServiceManager()->get('ViewRenderer');

                $content = $renderer->render($result);
                $cacheKey = $this->actionCacheKey($match);
                $cache->setItem($cacheKey, $content);
                $tags = $match->getParam('tags');
                if (is_array($tags)) {
                    $cache->setTags($cacheKey, $tags);
                }
            }
        }
    }

    /**
     * Generates valid page cache key
     *
     * @param RouteMatch $match
     * @param string $prefix
     * @return string
     */
    protected function pageCacheKey(RouteMatch $match, $prefix='pagecache_')
    {
        return  $prefix.str_replace('/','-',$match->getMatchedRouteName()).'_'.md5(serialize($match->getParams()));
    }

    /**
     * Generates valid action cache key
     *
     * @param RouteMatch $match
     * @param string $prefix
     * @return string
     */
    protected function actionCacheKey(RouteMatch $match, $prefix='actioncache_')
    {
        return $this->pageCacheKey($match, $prefix);
    }

    //endregion
}
