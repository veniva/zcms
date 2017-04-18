<?php

namespace Logic\Core;

use Doctrine\ORM\EntityManager;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Services\Language;

class Category
{
    const ERR_NO_CATEGORY = 'categ.none';
    const ERR_NO_ALIAS = 'categ.no-alias';
    
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
        if(!$alias){
            return [
                'status' => self::ERR_NO_ALIAS
            ];
        }
        
        $currentLanguageId = $this->language->getCurrentLanguage()->getId();
        $categoryRepo = $this->em->getRepository(CategoryEntity::class);
        $category = $categoryRepo->getCategoryByAliasAndLang(urldecode($alias), $currentLanguageId);
        if(!$category){
            return [
                'status' => self::ERR_NO_CATEGORY,
            ];
        }

        $categoryContent = $category->getSingleCategoryContent($currentLanguageId);
        $subCategories = $categoryRepo->findByParent($category);
        
        return [
            'status' => StatusCodes::SUCCESS,
            'category' => $category,
            'category_content' => $categoryContent,
            'sub_categories' => $subCategories,
            'lang_id' => $currentLanguageId
        ];
    }
}