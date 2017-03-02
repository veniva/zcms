<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Logic\Core\Model\Entity\Category;

class CategoryTree implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $languageService = $serviceManager->get('language');
        $entityManager = $serviceManager->get('entity-manager');
        $categoryRepository = $entityManager->getRepository(Category::class);
        return new \Logic\Core\Services\CategoryTree($languageService, $categoryRepository);
    }
}