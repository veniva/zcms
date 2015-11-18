<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
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
            return [];
        }
        $this->layout()->setVariables([
            'meta_title' => $categoryContent->getTitle()
        ]);

        $subCategories = $entityManager->getRepository(get_class($categoryEntity))->findByParent($categoryContent->getCategory());

        return new ViewModel([
            'category_content' => $categoryContent,
            'sub_categories' => $subCategories,
        ]);
    }
}
