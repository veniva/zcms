<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Admin\Form\Category as CategoryForm;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\CategoryContent;
use Logic\Core\Model\Entity\Lang;
use Application\Stdlib\Strings;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Filesystem\Filesystem;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class CategoryController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    public function __construct(ServiceLocatorInterface $serviceLocator, Filesystem $filesystem)
    {
        $this->setServiceLocator($serviceLocator);
        $this->fileSystem = $filesystem;
    }

    public function listAction()
    {
        return new ViewModel();
    }

    public function getList()
    {
        $parent = $this->params()->fromQuery('parent', 0);
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
            $content = $category->getSingleCategoryContent($defaultLangId);
            $categories[$i]['title'] = $content ? $category->getSingleCategoryContent($defaultLangId)->getTitle() : '';
            $categories[$i]['children_count'] = $categoryRepository->countChildren($category);
            $categories[$i]['sort'] = $category->getSort();
            $i++;
        }
        return new JsonModel([
            'title' => $this->translator->translate('Categories'),
            'lists' => $categories,
            'paginator' => $paginator,
            'breadcrumb' => $renderer->admin_breadcrumb(),
            'parent' => $parent,
        ]);
    }

    /**
     * Adds empty content in various languages to the category entity if necessary
     * Instantiates the forms and binds it to the data
     * @param Category $category
     * @param null &$form Referenced
     * @param bool $isNew
     * @return bool|JsonModel
     */
    protected function prepareFormAndLanguage($category, &$form, $isNew = false)
    {
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($category);
        $listingContent = !$isNew ? $category->getContent() : null;
        $form = new CategoryForm($entityManager, $listingContent);
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        $category2 = !$isNew ? $category : null;
        $parentElement = $form->get('parent');
        $parentElement->setEmptyOption($this->translator->translate('Top'));
        $parentElement->setValueOptions($categoryTree->getSelectOptions($category2));

        $form->bind($category);
        return true;
    }

    /**
     * Helps to render the category form for add/edit actions
     * @param Category $category
     * @param CategoryForm $form
     * @param $parentCategoryID
     * @return JsonModel
     */
    protected function renderCategData(Category $category, CategoryForm $form, $parentCategoryID)
    {
        $action = $category->getId() ? 'edit' : 'add';
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        $form->get('parent')->setValueOptions($categoryTree->getSelectOptions($category));
        $form->get('parent')->setValue($parentCategoryID);

        $viewModel = new ViewModel([
            'action' => $action,
            'id' => $category->getId(),
            'form' => $form,
        ]);
        $viewModel->setTemplate('admin/category/edit');
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');

        return new JsonModel(array(
            'title' => $this->translator->translate(ucfirst($action).' a category'),
            'form' => $renderer->render($viewModel),
            'parent' => (int)$parentCategoryID,
        ));
    }

    public function get($id)
    {
        $category = $this->getServiceLocator()->get('entity-manager')->find(get_class(new Category),$id);
        if(!$category){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('Wrong category ID')],
                'parent' => 0,
            ]);
        }
        $this->prepareFormAndLanguage($category, $form);

        return $this->renderCategData($category, $form, (int)$category->getParent());
    }

    public function update($id, $data)
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryClass = get_class(new Category);
        $category = $entityManager->find($categoryClass,$id);
        if(!$category){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('Wrong category ID')],
                'parent' => 0,
            ]);
        }
        $this->prepareFormAndLanguage($category, $form);

        $this->setParents($category, $data['parent']);
        $children = $entityManager->getRepository($categoryClass)->getChildren($category);
        foreach($children as $childEntity){
            $this->setParents($childEntity, $category->getId());
            $entityManager->persist($childEntity);
        }

        $entityManager = $this->getServiceLocator()->get('entity-manager');

        foreach($data['content'] as &$content){
            $content['alias'] = Strings::alias($content['title']);
        }

        $form->setData($data);
        if($form->isValid()){
            //v_todo - delete cache file in data/cache if cache enabled in module Application/config/module.config.php
            $entityManager->persist($category);
            $entityManager->flush();

            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $this->translator->translate('The category has been edited successfully')],
                'parent' => (int)$category->getParent(),
            ]);
        }

        return $this->renderCategData($category, $form, (int)$category->getParent());
    }

    protected function setParents(Category $category, $parentCategoryID)
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryRepository = $entityManager->getRepository(get_class($category));
        $relatedParentCategories = new ArrayCollection();
        if($parentCategoryID){
            $parentCategory = $categoryRepository->findOneById($parentCategoryID);
            if($parentCategory instanceof Category){
                $relatedParentCategories = new ArrayCollection($parentCategory->getParents()->toArray());
                $relatedParentCategories->add($parentCategory);
            }
        }
        $category->setParents($relatedParentCategories);
    }

    public function addJsonAction()
    {
        $parentCategoryID = $this->params()->fromQuery('parent', null);
        //check if there is an existing language before entering new category
        $langs = $this->getServiceLocator()->get('entity-manager')->getRepository(get_class(new Lang()))->countLanguages();
        if(!$langs){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('You must insert at least one language in order to add categories')],
                'parent' => $parentCategoryID
            ]);
        }
        $category = new Category();
        $this->prepareFormAndLanguage($category, $form, true);

        return $this->renderCategData($category, $form, $parentCategoryID);
    }

    public function create($data)
    {
        $langs = $this->getServiceLocator()->get('entity-manager')->getRepository(get_class(new Lang()))->countLanguages();
        if(!$langs){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('You must insert at least one language in order to add categories')],
                'parent' => $data['parent']
            ]);
        }
        $category = new Category();
        $this->setParents($category, $data['parent']);

        $this->prepareFormAndLanguage($category, $form, true);

        foreach($data['content'] as &$content){
            $content['alias'] = Strings::alias($content['title']);
        }
        $form->setData($data);
        if($form->isValid()){
            $entityManager = $this->getServiceLocator()->get('entity-manager');
            $entityManager->persist($category);
            $entityManager->flush();
            $this->getResponse()->setStatusCode(201);
            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $this->translator->translate('The new category was added successfully')],
                'parent' => (int)$data['parent'],
            ]);
        }

        return $this->renderCategData($category, $form, $data['parent']);
    }

    protected function addEmptyContent(Category $category)
    {
        $contentIDs = [];
        $languagesService = $this->getServiceLocator()->get('language');
        foreach($category->getContent() as $content){
            $contentIDs[] = $content->getLang()->getId();
        }

        $languages = $languagesService->getActiveLanguages();
        foreach($languages as $language){
            if(!in_array($language->getId(), $contentIDs)){
                new CategoryContent($category, $language);
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
                'parent' => 0
            ]);
        }

        //region Remove pages' images
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-core-dir'];
        $fileSystem = $this->fileSystem;
        $this->deleteListingImages($category, $fileSystem, $imgDir);
        //endregion

        $entityManager->remove($category);//contained listings are cascade removed from the ORM!!
        $entityManager->flush();
        //v_todo - delete cache file in data/cache if cache enabled in module Application/config/module.config.php

        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $this->translator->translate('The category and all the listings in it were removed successfully')],
            'parent' => (int)$category->getParent(),
        ]);
    }

    protected function deleteListingImages($category, Filesystem $fileSystem, $path)
    {
        $em = $this->getServiceLocator()->get('entity-manager');
        //dept-first recursion
        foreach($em->getRepository(get_class($category))->getChildren($category) as $subCategory){
            $this->deleteListingImages($subCategory, $fileSystem, $path);
        }

        foreach($category->getListings() as $listing){
            $fileSystem->remove($path.$listing->getId());
        }
    }
}
