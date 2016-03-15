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
use Application\Stdlib\Strings;
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

    public function getList()
    {
        $parent = $this->params()->fromQuery('parent_id', 0);
        $page = $this->params()->fromQuery('page', 1);
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
            'lists' => $categories,
            'paginator' => $paginator,
            'breadcrumb' => $renderer->breadcrumb('admin'),
            'parent_id' => $parent,
        ]);
    }

    protected function prepareGetData($category, &$form)
    {
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        if(!$category){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('Wrong category ID')],
                'parent_id' => 0,
            ]);
        }
        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($category);

        $form = new CategoryForm($entityManager, $category->getContent());
        $form->bind($category);
        return true;
    }

    /**
     * Helps to render the category form for add/edit actions
     * @param $category
     * @param $form
     * @param $parentCategory
     * @return JsonModel
     */
    protected function renderCategData($category, $form, $parentCategory)
    {
        $viewModel = new ViewModel([
            'action' => $category->getId() ? 'edit' : 'add',
            'id' => $category->getId(),
            'form' => $form,
            'parentCategoryName' => $this->getParentCategoryName($parentCategory),
        ]);
        $viewModel->setTemplate('admin/category/edit');
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');

        return new JsonModel(array(
            'title' => $this->translator->translate('Edit a category'),
            'form' => $renderer->render($viewModel),
            'parent_id' => $parentCategory instanceof Category ? $parentCategory->getId() : 0,
        ));
    }

    public function get($id)
    {
        $category = $this->getServiceLocator()->get('entity-manager')->find(get_class(new Category),$id);
        $result = $this->prepareGetData($category, $form);
        if($result !== true && $result instanceof JsonModel) return $result;

        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $parentCategory = $entityManager->find(get_class($category), (int)$category->getParent());

        return $this->renderCategData($category, $form, $parentCategory);
    }

    public function update($id, $data)
    {
        $category = $this->getServiceLocator()->get('entity-manager')->find(get_class(new Category),$id);
        $result = $this->prepareGetData($category, $form);
        if($result !== true && $result instanceof JsonModel) return $result;

        $entityManager = $this->getServiceLocator()->get('entity-manager');

        foreach($data['content'] as &$content){
            $content['alias'] = Strings::alias($content['title']);
        }

        $form->setData($data);
        if($form->isValid()){
            $entityManager->persist($category);
            $entityManager->flush();

            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $this->translator->translate('The category has been edited successfully')],
                'parent_id' => (int)$category->getParent(),
            ]);
        }

        $parentCategory = $entityManager->find(get_class($category), (int)$category->getParent());
        return $this->renderCategData($category, $form, $parentCategory);
    }

    protected function prepareAddData($parentCategoryID, &$form, &$categoryEntity, &$parentCategory)
    {
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $categoryEntity = new Category();
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));

        //check if there is an existing language before entering new category
        $langs = $entityManager->getRepository(get_class(new Lang()))->countLanguages();
        if(!$langs){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('You must insert at least one language in order to add categories')],
                'parent_id' => $parentCategoryID
            ]);
        }

        $parentCategory = ($parentCategoryID) ? $categoryRepository->findOneById($parentCategoryID) : null;
        if($parentCategory){
            $relatedParentCategories = $categoryRepository->getParentCategories($parentCategory);
            $categoryEntity->setParents($relatedParentCategories);
            $categoryEntity->setParent($parentCategory->getId());
            foreach($relatedParentCategories as $parent){
                $parent->addChild($categoryEntity);
            }
        }

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($categoryEntity);

        $form = new CategoryForm($entityManager);
        $form->bind($categoryEntity);
        return true;
    }

    public function addJsonAction()
    {
        $parentCategoryID = $this->params()->fromQuery('id', 0);
        $result = $this->prepareAddData($parentCategoryID, $form, $categoryEntity, $parentCategory);
        if($result !== true && $result instanceof JsonModel) return $result;

        return $this->renderCategData($categoryEntity, $form, $parentCategory);
    }

    public function create($data)
    {
        $result = $this->prepareAddData($data['id'], $form, $categoryEntity, $parentCategory);
        if($result !== true && $result instanceof JsonModel) return $result;

        foreach($data['content'] as &$content){
            $content['alias'] = Strings::alias($content['title']);
        }
        $form->setData($data);
        if($form->isValid()){
            $entityManager = $this->getServiceLocator()->get('entity-manager');
            $entityManager->persist($categoryEntity);
            $entityManager->flush();
            $this->getResponse()->setStatusCode(201);
            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $this->translator->translate('The new category was added successfully')],
                'parent_id' => $data['id'],
            ]);
        }

        return $this->renderCategData($categoryEntity, $form, $parentCategory);
    }

    protected function getParentCategoryName($parentCategory)
    {
        $defaultLangID = $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId();
        return $parentCategory instanceof Category ?
            $parentCategory->getSingleCategoryContent($defaultLangID)->getTitle() :
            $this->translator->translate('Top');
    }

    protected function addEmptyContent(Category $category)
    {
        $contentIDs = [];
        $defaultContent = null;
        $languagesService = $this->getServiceLocator()->get('language');
        foreach($category->getContent() as $content){
            $contentIDs[] = $content->getLang()->getId();
            if($content->getLang()->getId() == $languagesService->getDefaultLanguage()->getId())
                $defaultContent = $content;
        }

        $languages = $languagesService->getActiveLanguages();
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

    public function delete($id)
    {
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        $category = $entityManager->find(get_class(new Category), (int)$id);
        if(!$category){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('Category not found')],
                'parent_id' => 0
            ]);
        }

        $entityManager->remove($category);//contained listings are cascade removed from the ORM!!
        $entityManager->flush();

        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $this->translator->translate('The category and all the listings in it were removed successfully')],
            'parent_id' => (int)$category->getParent(),
        ]);
    }
}
