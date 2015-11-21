<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;


use Application\Service\Invokable\Misc;
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
        $currentLanguageId = Misc::getCurrentLanguage()->getId();
        $categoryEntity = $this->serviceLocator->get('category-entity');

        $category = $entityManager->getRepository(get_class($categoryEntity))->getCategoryByAliasAndLang(urldecode($alias), $currentLanguageId);
        if(!$category){
            $this->getResponse()->setStatusCode(404);
            return [];
        }
        $categoryContent = $category->getSingleCategoryContent($currentLanguageId);
        $this->layout()->setVariables([
            'meta_title' => $categoryContent->getTitle()
        ]);

        $subCategories = $entityManager->getRepository(get_class($categoryEntity))->findByParent($category);

        return new ViewModel([
            'category' => $category,
            'category_content' => $categoryContent,
            'sub_categories' => $subCategories,
            'langID' => $currentLanguageId
        ]);
    }
}
