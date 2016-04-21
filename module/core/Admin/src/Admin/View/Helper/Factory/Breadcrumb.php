<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\View\Helper\Factory;


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

        $entityManager = $serviceManager->get('entity-manager');
        $categoryContentEntity = $serviceManager->get('category-content-entity');

        $categoryContent = null;
        $id = $request->getQuery('parent', false);
        if($id !== false)
            $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByCategory($id);

        $defaultLang = $serviceManager->get('language')->getDefaultLanguage();
        return new \Admin\View\Helper\Breadcrumb($categoryContent, $defaultLang);
    }
}