<?php

namespace Logic\Core\Admin\Category;


use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Result;
use Logic\Core\Services\Language;

class CategoryList extends BaseLogic
{
    public function getList(EntityManagerInterface $em, Language $languageService, int $parent, int $page): Result
    {
        $categoryRepository = $em->getRepository(CategoryEntity::class);
        $categoriesPaginated = $categoryRepository->getPaginatedCategories($parent);
        $categoriesPaginated->setCurrentPageNumber($page);
        $defaultLangId = $languageService->getDefaultLanguage()->getId();
        
        $categories = [];
        $i = 0;
        foreach($categoriesPaginated as $category){
            $categories[$i]['id'] = $category->getId();
            $content = $category->getSingleCategoryContent($defaultLangId);
            $categories[$i]['title'] = $content ? $category->getSingleCategoryContent($defaultLangId)->getTitle() : '';
            $categories[$i]['children_count'] = $categoryRepository->countChildren($category);
            $categories[$i]['sort'] = $category->getSort();
            $i++;
        }
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'categories' => $categories,
            'categories_paginated' => $categoriesPaginated
        ]);
    }
}