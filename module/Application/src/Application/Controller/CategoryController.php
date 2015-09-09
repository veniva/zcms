<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 16:43
 */

namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CategoryController extends AbstractActionController
{
    public function showAction()
    {
        $alias = $this->params()->fromRoute('alias');
        if(!$alias){
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $entityManager = $this->serviceLocator->get('entity-manager');
        $categoryContentEntity = $this->serviceLocator->get('category-content-entity');
        $categoryEntity = $this->serviceLocator->get('category-entity');

        $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias($alias);
        if(!$categoryContent){
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $categoryID = $categoryContent->getCategory()->getId();

        $subCategories = $entityManager->getRepository(get_class($categoryEntity))->findByParentId($categoryID);

        return new ViewModel([
            'category_content' => $categoryContent,
            'sub_categories' => $subCategories,
        ]);
    }
}