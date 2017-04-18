<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\View\Helper\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class Breadcrumb implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $request = $container->get('Request');

        $entityManager = $container->get('entity-manager');
        $categoryContentEntity = $container->get('category-content-entity');

        $categoryContent = null;
        $id = $request->getQuery('parent', false);
        if($id !== false)
            $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByCategory($id);

        $defaultLang = $container->get('language')->getDefaultLanguage();
        return new \Admin\View\Helper\Breadcrumb($categoryContent, $defaultLang);
    }
}