<?php
namespace MainMenu;

use Application\Service\Invokable\Misc;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'addMenu'), 100);
    }

    public function addMenu(MvcEvent $event)
    {
        $viewModel = $event->getViewModel();
        $menuView = new ViewModel(['categories'=>$this->getTopCategories($event)]);
        $menuView->setTemplate('menu/layout');
        $viewModel->addChild($menuView, 'mainMenu');
    }

    public static function getTopCategories(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $entityManager = $serviceManager->get('entity-manager');
        $categoryEntity = $serviceManager->get('category-entity');
        $categRepo = $entityManager->getRepository(get_class($categoryEntity));
        $topCategs = [];
        if(!empty(Misc::getDefaultLanguage()->getId())){
            $topCategs = $categRepo->getCategoriesListings(0, Misc::getCurrentLanguage()->getId());
//            if(Misc::getCurrentLanguage()->getId() != Misc::getDefaultLanguage()->getId())//v_todo - fill in the missing content in newly added languages
//                $topCategs = $categRepo->translateCategoryTitles($topCategs, Misc::getCurrentLanguage()->getId());
        }

        return $topCategs;
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
