<?php

namespace Admin\Controller;


use Admin\Form\Category as CategoryForm;
use Application\Model\Entity\Category;
use Application\Model\Entity\CategoryContent;
use Application\Service\Invokable\Misc;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;

class CategoryController extends AbstractActionController
{
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function listAction()
    {
        $parent = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryEntity = $this->getServiceLocator()->get('category-entity');
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));

        $categoriesPaginated = $categoryRepository->getPaginatedCategories($parent);
        $categoriesPaginated->setCurrentPageNumber($page);

        $category = $parent ? $categoryRepository->findOneById($parent) : null;
        $categoryAlias = $category ? $category->getContent()->getAlias() : null;

        return [
            'title' => 'Categories',
            'categories' => $categoriesPaginated,
            'parent_id' => $parent,
            'category_alias' => $categoryAlias,
            'page' => $page,
            'categoryRepo' => $categoryRepository
        ];
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        if(empty($id))
            $this->redir()->toRoute('admin/category');

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $categoryEntity = new Category();
        $category = $this->getCategoryAndParent($id, $entityManager, $categoryEntity, $parentCategory);

        $categoryContentDefaultLanguageEntity = $category->getContent();

        //foreach active language add a content title field to the form
        $languages = Misc::getActiveLangs();
        $formClass = new CategoryForm($categoryContentDefaultLanguageEntity, $languages,
            $this->getServiceLocator()->get('translator'), $this->getServiceLocator()->get('validator-messages'));
        $form = $formClass->getForm();

        $contentLanguageEntities = [];
        foreach($languages as $language){
            if($language->getId() != Misc::getDefaultLanguage()->getId()){

                $categoryContentLanguageEntity = $category->getContent($language->getId());

                if(get_class($categoryContentLanguageEntity) == get_class($categoryContentDefaultLanguageEntity)){//if content on that language exists
                    $contentLanguageEntities[$language->getIsoCode()] = $categoryContentLanguageEntity;
                    $form->get('title_'.$language->getIsoCode())->setValue($categoryContentLanguageEntity->getTitle());
                }

            }
        }
        $form->get('sort')->setValue($category->getSort());
        $form->bind($categoryContentDefaultLanguageEntity);

        $request = $this->getRequest();
        if($request->isPost()){
            $post = ArrayUtils::iteratorToArray($request->getPost());
            $post['alias'] = Misc::alias($post['title']);
            $form->setData($post);
            if($form->isValid()){
                $category->setSort($form->getInputFilter()->getValue('sort'));

                foreach($languages as $language) {
                    if ($language->getId() != Misc::getDefaultLanguage()->getId()) {
                        $post['alias'] = Misc::alias($post['title_'.$language->getIsoCode()]);
                        $post['title'] = $post['title_'.$language->getIsoCode()];

                        if(isset($contentLanguageEntities[$language->getIsoCode()])){
                            $categoryContentLanguageEntity = $contentLanguageEntities[$language->getIsoCode()];
                            if(empty($post['title'])){
                                $entityManager->remove($categoryContentLanguageEntity);
                                continue;
                            }
                        }else{
                            $categoryContentLanguageEntity = new CategoryContent();//new entry
                            if(!empty($post['title'])){
                                $categoryContentLanguageEntity->setLang($language);
                            }
                        }

                        $form->bind($categoryContentLanguageEntity);
                        $form->setData($post);
                        if($form->isValid()){
                            $categoryContentLanguageEntity->setCategory($category);
                        }
                    }
                }

                $entityManager->persist($category);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The category has been edited successfully'));
                $this->redir()->toRoute('admin/category', [
                    'id' => isset($parentCategory) ? $parentCategory->getId() : null,
                    'page' => $page,
                ]);
            }
        }

        return [
            'form' => $form,
            'parentCategory' => $parentCategory,
            'page' => $page,
            'action' => 'Edit'
        ];
    }

    public function addAction()
    {
        $parentCategoryID = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);

        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryEntity = new Category();

        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));
        $parentCategory = ($parentCategoryID) ?
            $categoryRepository->findOneById($parentCategoryID) :
            null;

        if($parentCategory){
            $relatedParentCategories = $categoryRepository->getParentCategories($parentCategory);
            $categoryEntity->setParents($relatedParentCategories);
            $categoryEntity->setParent($parentCategory);
        }

        $categoryContentEntity = new CategoryContent();
        $categoryContentEntity->setLang(Misc::getDefaultLanguage());
        $categoryContentEntity->setCategory($categoryEntity);

        //foreach active language add a content title field to the form
        $languages = Misc::getActiveLangs();
        $formClass = new CategoryForm($categoryContentEntity, $languages,
            $this->getServiceLocator()->get('translator'), $this->getServiceLocator()->get('validator-messages'));
        $form = $formClass->getForm();
        $form->bind($categoryContentEntity);

        $request = $this->getRequest();
        if($request->isPost()){
            $post = ArrayUtils::iteratorToArray($request->getPost());
            $post['alias'] = Misc::alias($post['title']);
            $form->setData($post);
            if($form->isValid()){
                $categoryEntity->setSort($form->getInputFilter()->getValue('sort'));
                foreach($languages as $language){
                    if ($language->getId() != Misc::getDefaultLanguage()->getId()) {
                        $post['alias'] = Misc::alias($post['title_'.$language->getIsoCode()]);
                        $post['title'] = $post['title_'.$language->getIsoCode()];

                        if(!empty($post['title'])){
                            $categoryContentLanguageEntity = new CategoryContent();//new entry
                            $categoryContentLanguageEntity->setLang($language);

                            $form->bind($categoryContentLanguageEntity);
                            $form->setData($post);
                            if($form->isValid()){
                                $categoryContentLanguageEntity->setCategory($categoryEntity);//set to the new category to be cascade persisted by the ORM
                            }
                        }
                    }
                }
                $entityManager->persist($categoryEntity);
                $entityManager->flush();
                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The new category was added successfully'));
                $this->redir()->toRoute('admin/category', [
                    'id' => $parentCategoryID,
                    'page' => $page,
                ]);
            }
        }

        $viewModel = new ViewModel(array(
            'form' => $form,
            'parentCategory' => $parentCategory,
            'page' => $page,
            'action' => 'Add',
        ));
        $viewModel->setTemplate('admin/category/edit');

        return $viewModel;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        if(empty($id))
            $this->redir()->toRoute('admin/category');

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        $categoryEntity = new Category();
        $category = $this->getCategoryAndParent($id, $entityManager, $categoryEntity, $parentCategory);
        $entityManager->remove($category);//contained listings are cascade removed from the ORM!!
        $entityManager->flush();

        $this->flashMessenger()->addSuccessMessage($this->translator->translate('The category and all listings in it was removed successfully'));
        $this->redir()->toRoute('admin/category', [
            'id' => isset($parentCategory) ? $parentCategory->getId() : null,
            'page' => $page,
        ]);
    }

    /**
     * @param int $id
     * @param EntityManager $entityManager
     * @param Category $categoryEntity
     * @param $parentCategory
     * @return mixed
     */
    protected function getCategoryAndParent($id, EntityManager $entityManager, Category $categoryEntity, &$parentCategory)
    {
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));
        $category = $categoryRepository->findOneById($id);
        if($category->getParent() instanceof Category)
            $parentCategory = $categoryRepository->findOneById($category->getParent()->getId());
        return $category;
    }
}
