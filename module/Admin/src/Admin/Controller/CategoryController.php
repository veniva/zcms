<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Admin\Form\Category as CategoryForm;
use Application\Model\Entity\Category;
use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use Application\Service\Invokable\Misc;
use Doctrine\ORM\EntityManager;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;

class CategoryController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

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
        $languageService = $this->getServiceLocator()->get('language');
        $defaultLangId = $languageService->getDefaultLanguage()->getId();
        $categoryAlias = $category ? $category->getSingleCategoryContent($defaultLangId)->getAlias() : null;

        return [
            'title' => 'Categories',
            'categories' => $categoriesPaginated,
            'parent_id' => $parent,
            'category_alias' => $categoryAlias,
            'page' => $page,
            'categoryRepo' => $categoryRepository,
            'defaultLangId' => $defaultLangId
        ];
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        if(empty($id))
            return $this->redir()->toRoute('admin/category');

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $categoryEntity = new Category();
        $category = $this->getCategoryAndParent($id, $entityManager, $categoryEntity, $parentCategory);
        if(!$category)
            return $this->redir()->toRoute('admin/category');
        $languagesService = $serviceLocator->get('language');
        $languages = $languagesService->getActiveLanguages();

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($category, $languages);

        $form = new CategoryForm($entityManager, $category->getContent());
        $form->bind($category);

        $request = $this->getRequest();
        if($request->isPost()){
            $post = $request->getPost()->toArray();
            foreach($post['content'] as &$content){
                $content['alias'] = Misc::alias($content['title']);
            }
            $form->setData($post);
            if($form->isValid()){
                $entityManager->persist($category);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The category has been edited successfully'));
                return $this->redir()->toRoute('admin/category', [
                    'id' => isset($parentCategory) ? $parentCategory->getId() : null,
                    'page' => $page,
                ]);
            }
        }

        return [
            'form' => $form,
            'parentCategory' => $parentCategory,
            'page' => $page,
            'action' => 'Edit',
            'defaultLangId' => $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId()
        ];
    }

    public function addAction()
    {
        $parentCategoryID = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        //check if there is an existing language before entering new category
        $langs = $entityManager->getRepository(get_class(new Lang()))->countLanguages();
        if(!$langs){
            $this->flashMessenger()->addErrorMessage($this->translator->translate("You must insert at least one language in order to add categories"));
            return $this->redir()->toRoute('admin/category');
        }
        $categoryEntity = new Category();
        $languagesService = $serviceLocator->get('language');
        $languages = $languagesService->getActiveLanguages();

        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));
        $parentCategory = ($parentCategoryID) ?
            $categoryRepository->findOneById($parentCategoryID) :
            null;

        if($parentCategory){
            $relatedParentCategories = $categoryRepository->getParentCategories($parentCategory);
            $categoryEntity->setParents($relatedParentCategories);
            $categoryEntity->setParent($parentCategory->getId());
            foreach($relatedParentCategories as $parentCategory){
                $parentCategory->addChild($categoryEntity);
            }
        }

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($categoryEntity, $languages);

        $form = new CategoryForm($entityManager);
        $form->bind($categoryEntity);

        $request = $this->getRequest();
        if($request->isPost()){
            $post = $request->getPost()->toArray();
            foreach($post['content'] as &$content){
                $content['alias'] = Misc::alias($content['title']);
            }
            $form->setData($post);
            if($form->isValid()){
                $entityManager->persist($categoryEntity);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The new category was added successfully'));
                return $this->redir()->toRoute('admin/category', [
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

    protected function addEmptyContent(Category $category, \Doctrine\Common\Collections\Collection $languages)
    {
        $contentIDs = [];
        $defaultContent = null;
        $language = $this->getServiceLocator()->get('language');
        foreach($category->getContent() as $content){
            $contentIDs[] = $content->getLang()->getId();
            if($content->getLang()->getId() == $language->getDefaultLanguage()->getId())
                $defaultContent = $content;
        }

        foreach($languages as $language){
            if(!in_array($language->getId(), $contentIDs)){
                $newContent = new CategoryContent($category, $language);
                if($defaultContent){
                    $newContent->setAlias($defaultContent->getAlias());
                    $newContent->setTitle($defaultContent->getTitle());
                }
            }
        }
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

        $this->flashMessenger()->addSuccessMessage($this->translator->translate('The category and all the listings in it was removed successfully'));
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
     * @return Category|null
     */
    protected function getCategoryAndParent($id, EntityManager $entityManager, Category $categoryEntity, &$parentCategory)
    {
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));
        $category = $categoryRepository->findOneById($id);
        if(!$category) return null;

        if($category->getParent() instanceof Category)
            $parentCategory = $categoryRepository->findOneById($category->getParent()->getId());
        return $category;
    }
}
