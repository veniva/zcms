<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 12:29
 */

namespace Application\Service\Invokable;


class Layout
{

    public static function getTopCategories()
    {
        $entityManager = Misc::getStaticServiceLocator()->get('entity-manager');
        $categoryEntity = Misc::getStaticServiceLocator()->get('category-entity');
        $categRepo = $entityManager->getRepository(get_class($categoryEntity));
        $topCategs = [];
        if(!empty(Misc::getDefaultLanguage()->getId())){
            $topCategs = $categRepo->getCategoriesListings(0, Misc::getCurrentLanguage()->getId());
//            if(Misc::getCurrentLanguage()->getId() != Misc::getDefaultLanguage()->getId())//v_todo - fill in the missing content in newly added languages
//                $topCategs = $categRepo->translateCategoryTitles($topCategs, Misc::getCurrentLanguage()->getId());
        }

        return $topCategs;
    }

    public static function breadcrumb(&$title = null, $alias = null)
    {//V_TODO - rework all this breadcrumb with better approach
        if(!$alias){
            $route = Misc::getStaticRoute();
            if($route && $route->getMatchedRouteName() == 'category')
                $alias = $route->getParam('alias', null);
        }
        if(!$alias) return [];

        $entityManager = Misc::getStaticServiceLocator()->get('entity-manager');
        $categoryContentEntity = Misc::getStaticServiceLocator()->get('category-content-entity');

        //if category
        $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias(urldecode($alias));
        if(!$categoryContent) return [];

        $categoryParents = $categoryContent->getCategory()->getParents();
        $title = $categoryContent->getTitle();

        $aBcrumb[] = '';//the Top category
        foreach($categoryParents as $categoryParent){
            if(!$categoryParent->getId())
                break;

            $parentCategoryContent = $categoryParent->getSingleCategoryContent();

            if($alias != $parentCategoryContent->getAlias())
                $aBcrumb[] = [
                    'alias' => $parentCategoryContent->getAlias(),
                    'title' => $parentCategoryContent->getTitle(),
                    'id'    => $parentCategoryContent->getCategory()->getId(),
                ];
        }

        return $aBcrumb;
    }
}
