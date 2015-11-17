<?php
namespace Admin;

use Zend\Mvc\MvcEvent;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setLayout'));

        //if routed to admin but no controller found, show admin error page (not front-end error page)
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function(MvcEvent $event) {
            if($route = $event->getRouteMatch()){
                if($route->getMatchedRouteName() == 'admin/default'){
                    $viewModel = $event->getViewModel();
                    $viewModel->setTemplate('admin/layout');
                }
            }

        }, -200);

        $serviceManager->get('ViewHelperManager')->setFactory('formSelectCategory', function(){
            return new Form\View\Helper\SelectCategory();
        });
    }

    /**
     * Set new default layout for the Admin module
     * @param MvcEvent $e
     */
    public function setLayout(MvcEvent $e)
    {
        $routeMatches = $e->getRouteMatch();
        if(!$routeMatches) return;

        $controller = $routeMatches->getParam('controller');
        if(strpos($controller, __NAMESPACE__) === false){//if the controller does not belong to this module
            return;
        }

        $viewModel = $e->getViewModel();
        $viewModel->setTemplate('admin/layout');
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
