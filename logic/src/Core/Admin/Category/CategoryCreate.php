<?php

namespace Logic\Core\Admin\Category;


use Logic\Core\Adapters\Interfaces\ITranslator;
use Doctrine\ORM\EntityManager;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Form\Category as CategoryForm;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Result;
use Logic\Core\Services\Language;
use Logic\Core\Services\CategoryTree;
use Logic\Core\Stdlib\Strings;

class CategoryCreate extends BaseLogic
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
        $this->helpers = new CategoryHelpers($categoryTree, $this->languageService);

        parent::__construct($translator);
    }
    
    public function showForm(int $parentId): Result
    {
        if($parentId < 0){
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        $languagesNumber = $this->entityManager->getRepository(Lang::class)->countLanguages();
        if(!$languagesNumber){
            return $this->noLanguageResponse();
        }
        
        $categoryEntity = new Category();
        $this->helpers->setLanguageService($this->languageService);
        $form = $this->helpers->prepareFormWithLanguage($categoryEntity, $this->translator->translate('Top'), true);
        
        return $this->result(StatusCodes::SUCCESS, null, ['form' => $form, 'category' => $categoryEntity]);
    }

    public function create($data): Result
    {
        if(!isset($data['parent_id']) || !isset($data['content'])){
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        $languagesNumber = $this->entityManager->getRepository(Lang::class)->countLanguages();
        if(!$languagesNumber){
            return $this->noLanguageResponse();
        }

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = new Category();
        $parentCategory = $categoryRepository->find($data['parent_id']);
        $this->helpers->setParents($category, $parentCategory);

        $data['parent'] = $parentCategory;
        
        $form = $this->helpers->prepareFormWithLanguage($category, $this->translator->translate('Top'), true);

        foreach($data['content'] as &$content){
            $content['alias'] = Strings::alias($content['title']);
        }
        $form->setData($data);

        $entityManager = $this->entityManager;
        if($form->isFormValid($entityManager)){
            $entityManager->persist($category);
            $entityManager->flush();
            
            return $this->result(StatusCodes::SUCCESS, 'The new category was added successfully');
        }
        
        return $this->result(StatusCodes::ERR_INVALID_FORM, StatusMessages::ERR_INVALID_FORM_MSG, [
            'category' => $category,
            'form' => $form
        ]);
    }

    protected function noLanguageResponse():Result
    {
        return $this->result(self::ERR_NO_LANG, 'You must insert at least one language in order to add categories');
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