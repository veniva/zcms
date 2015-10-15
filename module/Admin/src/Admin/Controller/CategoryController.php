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
        $lang = Misc::getLangID();
        $parent = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryEntity = $this->getServiceLocator()->get('category-entity');
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));

        $categoriesPaginated = $categoryRepository->getPaginatedCategories($parent);
        $categoriesPaginated->setCurrentPageNumber($page);

        $category = $parent ? $categoryRepository->getCategory($parent, $lang) : null;
        $categoryAlias = $category ? $category['content']['alias'] : null;

        return [
            'title' => 'Categories',
            'categories' => $categoriesPaginated,
            'parent_id' => $parent,
            'category_alias' => $categoryAlias,
            'page' => $page,
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
        $formClass = new CategoryForm($categoryContentDefaultLanguageEntity, $languages);
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
        $form->bind($categoryContentDefaultLanguageEntity);

        $request = $this->getRequest();
        if($request->isPost()){
            $post = ArrayUtils::iteratorToArray($request->getPost());
            $post['alias'] = Misc::alias($post['title']);
            $form->setData($post);
            if($form->isValid()){
                $entityManager->persist($categoryContentDefaultLanguageEntity);

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
                                $categoryContentLanguageEntity->setCategory($category);
                                $categoryContentLanguageEntity->setLangId($language->getId());
                            }
                        }

                        $form->bind($categoryContentLanguageEntity);
                        $form->setData($post);
                        if($form->isValid()){
                            $entityManager->persist($categoryContentLanguageEntity);
                        }
                    }
                }

                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The category has been edited successfully'));
                $this->redir()->toRoute('admin/category', [
                    'id' => isset($parentCategory) ? $parentCategory->getId() : null,
                    'page' => $page,
                ]);
            }else{
                $this->flashMessenger()->addErrorMessage($this->translator->translate('There was an error in the new category name on the default language'));
                $this->redirect()->toUrl($_SERVER['REQUEST_URI']);//redirect to the same URL
            }
        }

        return [
            'form' => $form,
            'parentCategory' => $parentCategory,
            'page' => $page,
        ];
    }

    public function addAction()
    {
        $parentCategoryID = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);

        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryEntity = new Category();
        $categoryEntity->setParentId($parentCategoryID);
        $categoryContentEntity = new CategoryContent();
        $categoryContentEntity->setLangId(Misc::getDefaultLanguage()->getId());
        $categoryContentEntity->setCategory($categoryEntity);

        $parentCategory = ($parentCategoryID) ?
            $entityManager->getRepository(get_class($categoryEntity))->findOneById($parentCategoryID) :
            null;

        //foreach active language add a content title field to the form
        $languages = Misc::getActiveLangs();
        $formClass = new CategoryForm($categoryContentEntity, $languages);
        $form = $formClass->getForm();
        $form->bind($categoryContentEntity);

        $request = $this->getRequest();
        if($request->isPost()){
            $post = ArrayUtils::iteratorToArray($request->getPost());
            $post['alias'] = Misc::alias($post['title']);
            $form->setData($post);
            if($form->isValid()){
                $entityManager->persist($categoryEntity);
                $entityManager->persist($categoryContentEntity);

                foreach($languages as $language){
                    if ($language->getId() != Misc::getDefaultLanguage()->getId()) {
                        $post['alias'] = Misc::alias($post['title_'.$language->getIsoCode()]);
                        $post['title'] = $post['title_'.$language->getIsoCode()];

                        if(!empty($post['title'])){
                            $categoryContentLanguageEntity = new CategoryContent();//new entry
                            $categoryContentLanguageEntity->setCategory($categoryEntity);
                            $categoryContentLanguageEntity->setLangId($language->getId());

                            $form->bind($categoryContentLanguageEntity);
                            $form->setData($post);
                            if($form->isValid()){
                                $entityManager->persist($categoryContentLanguageEntity);
                            }
                        }
                    }
                }
                $entityManager->flush();
                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The new category was added successfully'));
                $this->redir()->toRoute('admin/category', [
                    'id' => $parentCategoryID,
                    'page' => $page,
                ]);
            }else{
                $this->flashMessenger()->addErrorMessage($this->translator->translate('There was an error in the new category name on the default language'));
                $this->redirect()->toUrl($_SERVER['REQUEST_URI']);//redirect to the same URL
            }
        }

        $viewModel = new ViewModel(array(
            'form' => $form,
            'parentCategory' => $parentCategory,
            'page' => $page,
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
        $parentCategory = $categoryRepository->findOneById($category->getParentId());
        return $category;
    }
}
