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
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class CategoryController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function listAction()
    {
        return new ViewModel();
    }

    public function listJsonAction()
    {
        $parent = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryEntity = $this->getServiceLocator()->get('category-entity');
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));

        $categoriesPaginated = $categoryRepository->getPaginatedCategories($parent);
        $categoriesPaginated->setCurrentPageNumber($page);
        $languageService = $this->getServiceLocator()->get('language');
        $defaultLangId = $languageService->getDefaultLanguage()->getId();

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $paginator = $renderer->paginationControl($categoriesPaginated, 'Sliding', 'paginator/sliding_category_ajax', ['id' => $parent]);

        $categories = [];
        $i = 0;
        foreach($categoriesPaginated as $category){
            $categories[$i]['id'] = $category->getId();
            $categories[$i]['title'] = $category->getSingleCategoryContent($defaultLangId)->getTitle();
            $categories[$i]['children_count'] = $categoryRepository->countChildren($category->getId());
            $categories[$i]['sort'] = $category->getSort();
            $i++;
        }
        return new JsonModel([
            'title' => $this->translator->translate('Categories'),
            'categories' => $categories,
            'page' => $page,
            'paginator' => $paginator,
            'breadcrumb' => $renderer->breadcrumb('admin'),
            'parent_id' => $parent,
        ]);
    }

    public function editJsonAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        if(empty($id)){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('There was missing/wrong parameter in the request')],
                'parent_id' => '0',
            ]);
        }

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $categoryEntity = new Category();
        $category = $this->getCategoryAndParent($id, $entityManager, $categoryEntity, $parentCategoryID);
        if(!$category){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('Wrong category ID')],
                'parent_id' => '0',
            ]);
        }
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

                return new JsonModel([
                    'message' => ['type' => 'success', 'text' => $this->translator->translate('The category has been edited successfully')],
                    'parent_id' => $parentCategoryID,
                ]);
            }
        }
        $defaultLangID = $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId();
        $parentCategory = $entityManager->find(get_class($categoryEntity), $parentCategoryID);
        $parentCategoryName = $parentCategory instanceof \Application\Model\Entity\Category ?
            $parentCategory->getSingleCategoryContent($defaultLangID)->getTitle() :
            $this->translator->translate('Top');

        $viewModel = new ViewModel([
            'action' => 'edit',
            'id' => $id,
            'form' => $form,
            'parentCategoryName' => $parentCategoryName,
            'parent_id' => $parentCategoryID,
            'page' => $page,
        ]);
        $viewModel->setTemplate('admin/category/edit');
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');

        return new JsonModel([
            'title' => $this->translator->translate('Edit a category'),
            'page' => $page,
            'form' => $renderer->render($viewModel),
        ]);
    }

    public function addJsonAction()
    {
        $parentCategoryID = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        //check if there is an existing language before entering new category
        $langs = $entityManager->getRepository(get_class(new Lang()))->countLanguages();
        if(!$langs){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('You must insert at least one language in order to add categories')],
                'parent_id' => $parentCategoryID,
            ]);
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
            foreach($relatedParentCategories as $parent){
                $parent->addChild($categoryEntity);
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
                return new JsonModel([
                    'message' => ['type' => 'success', 'text' => $this->translator->translate('The new category was added successfully')],
                    'parent_id' => $parentCategoryID,
                ]);
            }
        }
        $defaultLangID = $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId();
        $parentCategoryName = $parentCategory instanceof \Application\Model\Entity\Category ?
            $parentCategory->getSingleCategoryContent($defaultLangID)->getTitle() :
            $this->translator->translate('Top');

        $viewModel = new ViewModel(array(
            'action' => 'add',
            'form' => $form,
            'parentCategoryName' => $parentCategoryName,
            'parent_id' => $parentCategoryID,
            'page' => $page,
        ));
        $viewModel->setTemplate('admin/category/edit');
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');

        return new JsonModel([
            'title' => $this->translator->translate('Add a category'),
            'page' => $page,
            'form' => $renderer->render($viewModel),
        ]);
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

    public function deleteJsonAction()
    {
        if(!$this->getRequest()->isPost()) return false;
        $id = $this->params()->fromPost('id', 0);
        $page = $this->params()->fromPost('page', 1);
        if(empty($id)){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('There was missing/wrong parameter in the request')],
                'parent_id' => '0',
                'page' => $page,
            ]);
        }

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        $categoryEntity = new Category();
        $category = $this->getCategoryAndParent($id, $entityManager, $categoryEntity, $parentCategoryID);
        $entityManager->remove($category);//contained listings are cascade removed from the ORM!!
        $entityManager->flush();

        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $this->translator->translate('The category and all the listings in it were removed successfully')],
            'parent_id' => $parentCategoryID,
            'page' => $page,
        ]);
    }

    /**
     * @param int $id
     * @param EntityManager $entityManager
     * @param Category $categoryEntity
     * @param $parentCategoryID
     * @return Category|null
     */
    protected function getCategoryAndParent($id, EntityManager $entityManager, Category $categoryEntity, &$parentCategoryID)
    {
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));
        $category = $categoryRepository->findOneById($id);
        if(!$category) return null;

        $parentCategoryID = $category->getParent() ? $category->getParent() : '0';
        return $category;
    }
}
