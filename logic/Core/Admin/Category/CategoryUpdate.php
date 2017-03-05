<?php

namespace Logic\Core\Admin\Category;


use Logic\Core\Adapters\Interfaces\ITranslator;
use Doctrine\ORM\EntityManager;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Stdlib\Strings;
use Logic\Core\Form\Category as CategoryForm;
use Logic\Core\Services\Language;
use Logic\Core\Services\CategoryTree;
use Doctrine\Common\Collections\ArrayCollection;
use Logic\Core\Model\CategoryRepository;

class CategoryUpdate extends BaseLogic
{
    const ERR_CATEGORY_NOT_FOUND = 'u-cat.categ-not-found';
    
    /** @var  ITranslator */
    protected $translator;
    
    /** @var EntityManager */
    protected $entityManager;

    /** @var Language */
    protected $languageService;

    /** @var CategoryTree */
    protected $categoryTree;
    
    /** @var CategoryForm */
    protected $categoryForm;

    /** @var CategoryHelpers */
    protected $helpers;

    public function __construct(EntityManager $entityManager, ITranslator $translator, CategoryTree $categoryTree, Language $language = null)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->categoryTree = $categoryTree;
        $this->languageService = $language ? $language : new Language();
        $this->categoryForm = new CategoryForm();
        $this->helpers = new CategoryHelpers($categoryTree, $this->languageService);

        parent::__construct($translator);
    }

    /**
     * Show the category form
     * @param integer $id The category ID
     * @return array
     */
    public function get($id)
    {
        if(!$id){
            return $this->response(StatusCodes::ERR_INVALID_PARAM, 'Wrong category ID');
        }

        /** @var Category $category */
        $category = $this->entityManager->find(CategoryEntity::class, $id);
        if(!$category){
            return $this->response(self::ERR_CATEGORY_NOT_FOUND, 'Wrong category ID');
        }
        
        $form = $this->helpers->prepareFormWithLanguage($category, $this->translator->translate('Top'));
        
        return $this->response(StatusCodes::SUCCESS, null, ['form' => $form, 'category' => $category]);
    }

    /**
     * Handle the update of the category
     * @param integer $id
     * @param array $data
     * @return array
     */
    public function update(int $id, $data)
    {
        /** @var Category $category */
        $category = $this->entityManager->find(Category::class, $id);
        if(!$category){
            return $this->response(self::ERR_CATEGORY_NOT_FOUND, 'Wrong category ID provided');
        }

        $form = $this->helpers->prepareFormWithLanguage($category, $this->translator->translate('Top'));
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->setParents($category, $categoryRepository, $data['parent']);

        $children = $categoryRepository->getChildren($category);
        foreach($children as $childEntity){
            $this->setParents($childEntity, $categoryRepository, $category->getId());
            $this->entityManager->persist($childEntity);
        }

        foreach($data['content'] as &$content){
            $content['alias'] = Strings::alias($content['title']);
        }

        $form->setData($data);
        if($form->isFormValid($this->entityManager, $category->getContent())){
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->response(StatusCodes::SUCCESS, 'The category has been edited successfully', ['parent' => (int)$category->getParent()]);
        }
        
        return $this->response(StatusCodes::ERR_INVALID_FORM, null, ['form' => $form, 'category' => $category]);
    }

    public function setParents(CategoryEntity $category, CategoryRepository $categoryRepository, $parentCategoryID)
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
     * @return CategoryHelpers
     */
    public function getHelpers()
    {
        return $this->helpers;
    }

    /**
     * @param CategoryHelpers $helpers
     * @return CategoryCreate
     */
    public function setHelpers(CategoryHelpers $helpers)
    {
        $this->helpers = $helpers;
        return $this;
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
        $this->helpers->setCategoryForm($categoryForm);
        return $this;
    }
}