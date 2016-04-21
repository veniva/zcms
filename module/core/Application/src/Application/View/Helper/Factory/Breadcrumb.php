<?php

namespace Application\View\Helper\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Breadcrumb implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $serviceManager = $helperPluginManager->getServiceLocator();
        $request = $serviceManager->get('Request');
        $route = $serviceManager->get('Router');
        $routeMatch = $route->match($request);

        $entityManager = $serviceManager->get('entity-manager');
        $categoryContentEntity = $serviceManager->get('category-content-entity');

        $categoryContent = null;
        $alias = $routeMatch->getParam('alias', false);
        if($alias !== false)
            $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias(urldecode($alias));

        $defaultLang = $serviceManager->get('language')->getDefaultLanguage();
        return new \Application\View\Helper\Breadcrumb($categoryContent, $defaultLang);
    }
}