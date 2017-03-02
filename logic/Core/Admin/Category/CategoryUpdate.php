<?php

namespace Logic\Core\Admin\Category;


use Logic\Core\Adapters\Interfaces\ITranslator;
use Doctrine\ORM\EntityManager;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Stdlib\Strings;
use Logic\Core\Form\Category as CategoryForm;
use Logic\Core\Services\Language;
use Logic\Core\Services\CategoryTree;
use Logic\Core\Model\Entity\CategoryContent;
use Doctrine\Common\Collections\ArrayCollection;
use Logic\Core\Model\CategoryRepository;

class CategoryUpdate
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

    public function __construct(EntityManager $entityManager, ITranslator $translator, CategoryTree $categoryTree)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->categoryTree = $categoryTree;
        $this->languageService = new Language();
        $this->categoryForm = new CategoryForm();
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

    /**
     * Show the category form
     * @param integer $id The category ID
     * @return array
     */
    public function get($id)
    {
        if(!$id){
            return [
                'status' => StatusCodes::ERR_INVALID_PARAM,
                'message' => $this->translator->translate('Wrong category ID')
            ];
        }
        /** @var Category $category */
        $category = $this->entityManager->find(CategoryEntity::class, $id);
        if(!$category){
            return [
                'status' => self::ERR_CATEGORY_NOT_FOUND,
                'message' => $this->translator->translate('Wrong category ID')
            ];
        }
        
        $form = $this->prepareFormWithLanguage($category, $this->translator->translate('Top'));
        
        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $form,
            'category' => $category
        ];
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
            return [
                'status' => self::ERR_CATEGORY_NOT_FOUND,
                'message' => $this->translator->translate('Wrong category ID provided')
            ];
        }

        $form = $this->prepareFormWithLanguage($category, $this->translator->translate('Top'));
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

            return [
                'status' => StatusCodes::SUCCESS,
                'message' => $this->translator->translate('The category has been edited successfully'),
                'parent' => (int)$category->getParent(),
            ];
        }
        
        return [
            'status' => StatusCodes::ERR_INVALID_FORM,
            'form' => $form,
            'category' => $category
        ];
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
        foreach((array)$languages as $language){
            if(!in_array($language->getId(), $contentIDs)){
                new CategoryContent($category, $language);
            }
        }
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
}