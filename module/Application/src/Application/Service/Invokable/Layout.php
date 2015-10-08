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
    public static function getAllLangs()
    {
        $entityManager = Misc::getStaticServiceLocator()->get('entity-manager');
        $langEntity = Misc::getStaticServiceLocator()->get('lang-entity');
        $languages = $entityManager->getRepository(get_class($langEntity))->getActiveLangs();
        return $languages;
    }

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
        if(!$alias) return '';

        $entityManager = Misc::getStaticServiceLocator()->get('entity-manager');
        $categoryRelationsEntity = Misc::getStaticServiceLocator()->get('category-relations-entity');
        $categoryContentEntity = Misc::getStaticServiceLocator()->get('category-content-entity');

        //if category
        $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias($alias);
        if(!$categoryContent) return '';

        $categoryRelations = $entityManager->getRepository(get_class($categoryRelationsEntity))->findByCategory($categoryContent->getCategory());
        $title = $categoryContent->getTitle();

        $aBcrumb[] = '';//the Top category
        foreach($categoryRelations as $categoryRelation){
            $parentCategoryContent = $categoryRelation->getParent()->getContent();

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
