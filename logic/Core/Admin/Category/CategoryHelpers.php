<?php

namespace Logic\Core\Admin\Category;


use Doctrine\Common\Collections\ArrayCollection;
use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Form\Category as CategoryForm;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\CategoryContent;
use Logic\Core\Services\CategoryTree;
use Logic\Core\Services\Language;

class CategoryHelpers
{
    /** @var CategoryTree */
    protected $categoryTree;

    /** @var CategoryForm */
    protected $categoryForm;

    /** @var Language */
    protected $languageService;
    
    public function __construct(CategoryTree $categoryTree, Language $language = null)
    {
        $this->categoryForm = new CategoryForm();
        $this->languageService = $language ? $language : new Language();
        $this->categoryTree = $categoryTree;
    }
    
    /**
     * @param CategoryEntity $category
     * @param $topName
     * @param bool $isNew
     * @return CategoryForm
     */
    public function prepareFormWithLanguage(CategoryEntity $category, $topName, $isNew = false)
    {
        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($category);
        $form = $this->categoryForm;
        $form->bind($category);
        $category2 = !$isNew ? $category : null;

        $parentElement = $form->get('parent');
        $parentElement->setEmptyOption($topName);
        $parentElement->setValueOptions($this->categoryTree->getSelectOptions($category2));

        return $form;
    }

    protected function addEmptyContent(CategoryEntity $category)
    {
        $contentIDs = [];
        foreach($category->getContent() as $content){
            $contentIDs[] = $content->getLang()->getId();
        }

        $languages = $this->languageService->getActiveLanguages();
        if($languages !== null){
            foreach($languages as $language){
                if(!in_array($language->getId(), $contentIDs)){
                    new CategoryContent($category, $language);
                }
            }
        }
    }

    public function setParents(Category $category, CategoryRepository $categoryRepository, $parentCategoryID)
    {
        $relatedParentCategories = new ArrayCollection();
        if($parentCategoryID){
            $parentCategory = $categoryRepository->findOneById($parentCategoryID);
            if($parentCategory instanceof CategoryEntity){
                $relatedParentCategories = new ArrayCollection($parentCategory->getParents()->toArray());
                $relatedParentCategories->add($parentCategory);
            }
        }
        $category->setParents($relatedParentCategories);
    }

    /**
     * @param Language $languageService
     * @return self
     */
    public function setLanguageService(Language $languageService)
    {
        $this->languageService = $languageService;
        return $this;
    }

    /**
     * @param CategoryForm $categoryForm
     * @return CategoryUpdate
     */
    public function setCategoryForm($categoryForm)
    {
        $this->categoryForm = $categoryForm;
        return $this;
    }
}