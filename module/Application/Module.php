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
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();

        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'globalLayoutVars'));
        $dbAdapter = $serviceManager->get('dbadapter');
        GlobalAdapterFeature::setStaticAdapter($dbAdapter);

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
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
                $routeMatch = $e->getRouteMatch();
                $controller = $routeMatch->getParam('controller');
                $controller = strtolower(substr($controller, strrpos($controller, '\\')+1));
                $action = $routeMatch->getParam('action');
                $viewModel->setVariables(['controller' => $controller, 'action' => $action]);
            }
        }
    }
}
