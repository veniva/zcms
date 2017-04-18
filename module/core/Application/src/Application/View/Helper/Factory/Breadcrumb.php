<?php

namespace Application\View\Helper\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class Breadcrumb implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $request = $container->get('Request');
        $route = $container->get('Router');
        $routeMatch = $route->match($request);

        $entityManager = $container->get('entity-manager');
        $categoryContentEntity = $container->get('category-content-entity');

        $categoryContent = null;
        $alias = $routeMatch->getParam('alias', false);
        if($alias !== false)
            $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias(urldecode($alias));

        $defaultLang = $container->get('language')->getDefaultLanguage();
        return new \Application\View\Helper\Breadcrumb($categoryContent, $defaultLang);
    }
}