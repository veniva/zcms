<?php

namespace Admin\Controller;


use Application\Service\Invokable\Misc;
use Zend\Mvc\Controller\AbstractActionController;

class CategoryController extends AbstractActionController
{
    public function listAction()
    {
        $lang = Misc::getLangID();
        $parent = $this->params()->fromRoute('parent_id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $categoryEntity = $serviceLocator->get('category-entity');
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));

        $categoriesPaginated = $categoryRepository->getPaginatedCategories($parent);
        $categoriesPaginated->setCurrentPageNumber($page);

        $category = $parent ? $categoryRepository->getCategory($parent, $lang) : null;
        $categoryAlias = $category ? $category['content']['alias'] : null;

        return [
            'title' => 'Categories',
            'categories' => $categoriesPaginated,
            'parent_id' => $parent,
            'category_alias' => $categoryAlias,
        ];
    }
}
