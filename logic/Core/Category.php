<?php

namespace Logic\Core;


use Doctrine\ORM\EntityManager;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Services\Language;

class Category
{
    /** @var  EntityManager */
    protected $em;
    
    /** @var  Language */
    protected $language;
    
    public function __construct(EntityManager $em, Language $language)
    {
        $this->em = $em;
        $this->language = $language;
    }
    
    public function process(string $alias):array
    {
        $currentLanguageId = $this->language->getCurrentLanguage()->getId();
        $category = $this->em->getRepository(CategoryEntity::class)->getCategoryByAliasAndLang(urldecode($alias), $currentLanguageId);
        if(!$category){
            return [
                'error' => true,
            ];
        }

        $categoryContent = $category->getSingleCategoryContent($currentLanguageId);
        $subCategories = $this->em->getRepository(CategoryEntity::class)->findByParent($category);
        
        return [
            'error' => false,
            'category' => $category,
            'category_content' => $categoryContent,
            'sub_categories' => $subCategories,
            'lang_id' => $currentLanguageId
        ];
    }
}