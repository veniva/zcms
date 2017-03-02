<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Services;

use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Category;

//V_TODO - incorporate this to be part of the CategoryRepository as it's a model and not service
class CategoryTree
{
    /**
     * @var array Categories as detailed array
     */
    protected $categories = array();

    /**
     * @var array Categories array handy for setting select options
     */
    protected $categoriesAsOptions = array();

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Language
     */
    protected $languageService;

    public function __construct(Language $languageService, CategoryRepository $categoryRepository, $parentId = null)
    {
        $this->languageService = $languageService;
        $this->categoryRepository = $categoryRepository;

        $this->setCategories($parentId);
    }
    
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Form hierarchical category tree walking all the categories recursively
     *
     * @param null|int $parentId
     * @param int $level What should be the hyphenated indentation
     * @return void
     */
    public function setCategories($parentId = null, $level = 0)
    {
        $languageService = $this->languageService;
        $defaultLangID = $languageService->getDefaultLanguage()->getId();
        $childrenCategories = $this->categoryRepository->getCategoriesByParent($parentId);
        foreach($childrenCategories as $category){
            $indent = '';
            foreach(range(0, $level) as $in){
                if($in)
                    $indent .= '-';
            }
            $title = $category->getSingleCategoryContent($defaultLangID) ? $category->getSingleCategoryContent($defaultLangID)->getTitle() : '';
            $this->categories[$category->getId()] = [
                'id' => $category->getId(),
                'title' => $title,
                'indent' => $indent
            ];
            $this->categoriesAsOptions[$category->getId()] = $indent.$title;

            $this->setCategories($category->getId(), $level+1);
        }
    }

    /**
     * @param Category|null $category
     * @return array
     */
    public function getSelectOptions(Category $category = null)
    {
        if($category && $category->getId()){//if the category is not new category
            return $this->getAllButChildren($category);
        }
        
        return $this->categoriesAsOptions;
    }

    /**
     * Get all the categories except the current one and it's children
     * @param Category $category
     * @return array Categories array handy for setting form select options [id] => intent+title
     */
    protected function getAllButChildren(Category $category)
    {
        $childIds = [];
        if(!empty($category->getId())){
            $categoryChildren = $this->categoryRepository->getChildren($category);
            foreach($categoryChildren as $child){
                $childIds[] = $child->getId();
            }
        }

        $allButOwnCategs = [];
        foreach($this->categoriesAsOptions as $id => $categs){
            if($id != $category->getId() && !in_array($id, $childIds)){
                $allButOwnCategs[$id] = $this->categoriesAsOptions[$id];
            }
        }
        return $allButOwnCategs;
    }
}