<?php

namespace Admin\CategoryTree;


use Application\Model\Entity\Category;
use Zend\ServiceManager\ServiceLocatorInterface;

class CategoryTree
{

    protected $categories = array();

    /**
     * @var \Application\Model\CategoryRepository
     */
    protected $categoryRepo;

    public function __construct(ServiceLocatorInterface $serviceManager, $parentId = null)
    {
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
        $childrenCategories = $this->categoryRepo->findBy(['parent' => $parentId]);
        foreach($childrenCategories as $category){
            $indent = '';
            foreach(range(0, $level) as $in){
                if($in)
                    $indent .= '-';
            }
            $this->categories[$category->getId()] = [
                'id' => $category->getId(),
                'title' => $category->getContent()->getTitle(),
                'indent' => $indent
            ];
            $this->setCategories($category->getId(), $level+1);
        }
    }

    public function getCategories()
    {
        return $this->categories;
    }
}