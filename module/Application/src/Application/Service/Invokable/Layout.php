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
        $topCategs = $categRepo->getCategoriesListings(0, Misc::getDefaultLanguageID());
        if(Misc::getLangID() != Misc::getDefaultLanguageID())
            $topCategs = $categRepo->translateCategoryTitles($topCategs, Misc::getLangID());
        return $topCategs;
    }

    public static function breadcrumb(&$title = null, $alias = null)
    {//V_TODO - rework all this breadcrumb with better approach
        if(!$alias){
            $route = Misc::getStaticRoute();
            $alias = $route->getParam('alias', null);
        }
        if(!$alias) return [];

        $entityManager = Misc::getStaticServiceLocator()->get('entity-manager');
        $categoryContentEntity = Misc::getStaticServiceLocator()->get('category-content-entity');

        //if category
        $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias($alias);
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
