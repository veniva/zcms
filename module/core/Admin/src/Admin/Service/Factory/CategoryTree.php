<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Logic\Core\Model\Entity\Category;

class CategoryTree implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $languageService = $container->get('language');
        $entityManager = $container->get('entity-manager');
        $categoryRepository = $entityManager->getRepository(Category::class);
        return new \Logic\Core\Services\CategoryTree($languageService, $categoryRepository);
    }
}