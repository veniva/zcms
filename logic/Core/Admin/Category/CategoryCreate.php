<?php

namespace Logic\Core\Admin\Category;


use Logic\Core\Adapters\Interfaces\ITranslator;
use Doctrine\ORM\EntityManager;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Form\Category as CategoryForm;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Services\Language;
use Logic\Core\Services\CategoryTree;

class CategoryCreate
{
    const ERR_NO_LANG = 'c-cat.no-lang';
    
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
        $this->helpers = new CategoryHelpers($categoryTree);
    }
    
    public function showForm(int $parentId)
    {
        if($parentId < 0){
            return [
                'status' => StatusCodes::ERR_INVALID_PARAM,
                'message' => $this->translator->translate('Invalid parameter provided')
            ];
        }
        
        $languagesNumber = $this->entityManager->getRepository(Lang::class)->countLanguages();
        if(!$languagesNumber){
            return [
                'status' => self::ERR_NO_LANG,
                'message' => $this->translator->translate('You must insert at least one language in order to add categories')
            ];
        }
        
        $categoryEntity = new Category();
        $this->helpers->setLanguageService($this->languageService);
        $form = $this->helpers->prepareFormWithLanguage($categoryEntity, $this->translator->translate('Top'), true);
        
        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $form,
            'category' => $categoryEntity
        ];
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
     * @return CategoryCreate
     */
    public function setLanguageService(Language $languageService)
    {
        $this->languageService = $languageService;
        return $this;
    }

    /**
     * @param CategoryForm $categoryForm
     * @return CategoryCreate
     */
    public function setCategoryForm($categoryForm)
    {
        $this->categoryForm = $categoryForm;
        $this->helpers->setCategoryForm($categoryForm);
        return $this;
    }
    
    
}