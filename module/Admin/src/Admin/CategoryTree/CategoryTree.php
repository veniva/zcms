<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\CategoryTree;

use Application\Model\Entity\Category;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @var \Application\Model\CategoryRepository
     */
    protected $categoryRepo;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;

    public function __construct(ServiceLocatorInterface $serviceManager, $parentId = null)
    {
        $this->serviceManager = $serviceManager;
        $entityManager = $serviceManager->get('entity-manager');
        $this->categoryRepo = $entityManager->getRepository(get_class(new Category()));

        $this->setCategories($parentId);
    }

    /**
     * Form hierarchical category tree walking all the categories recursively
     *
     * @param null|int $parentId
     * @param int $level What should be the hyphenated indentation
     * @return void
     */
    protected function setCategories($parentId = null, $level = 0)
    {
        $languageService = $this->serviceManager->get('language');
        $defaultLangID = $languageService->getDefaultLanguage()->getId();
        $childrenCategories = $this->categoryRepo->findBy(['parent' => $parentId]);
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

    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array Categories array handy for setting select options [id] => intent+title
     */
    public function getCategoriesAsOptions()
    {
        return $this->categoriesAsOptions;
    }

    /**
     * Re-sets the whole category tree on demand
     */
    public function reSetCategories()
    {
        $this->setCategories();
    }
}