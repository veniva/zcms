<?php

namespace Admin\Controller;


use Application\Service\Invokable\Misc;
use Zend\Mvc\Controller\AbstractActionController;

class CategoryController extends AbstractActionController
{
    public function indexAction()
    {
        $lang = Misc::getLangID();
        $parent = $this->params()->fromRoute('id', 0);
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $categoryEntity = $serviceLocator->get('category-entity');
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));

        $categories = $categoryRepository->getCategories($lang, $parent);
        $category = $parent ? $categoryRepository->getCategory($parent, $lang) : null;
        $categoryAlias = $category ? $category['content']['alias'] : null;

        return [
            'title' => 'Categories',
            'categories' => $categories,
            'parent-alias' => $parent,
            'category_alias' => $categoryAlias,
        ];
    }
}
