<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Zend\Translator;
use Logic\Core\Admin\Category\CategoryCreate;
use Logic\Core\Admin\Category\CategoryDelete;
use Logic\Core\Admin\Category\CategoryUpdate;
use Logic\Core\Form\Category as CategoryForm;
use Logic\Core\Admin\Category\CategoryList as CategoryLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Services\Language;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Logic\Core\Services\CategoryTree;

class CategoryController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function listAction()
    {
        return new ViewModel();
    }

    public function getList()
    {
        $parent = (int)$this->params()->fromQuery('parent', 0);
        $page = (int)$this->params()->fromQuery('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $languageService = $this->getServiceLocator()->get('language');
        $categoryLogic = new CategoryLogic();
        $result = $categoryLogic->getList($entityManager, $languageService, $parent, $page);

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $paginator = $renderer->paginationControl(
            $result->get('categories_paginated'),
            'Sliding',
            'paginator/sliding_category_ajax',
            ['id' => $parent]
        );

        return new JsonModel([
            'title' => $this->translator->translate('Categories'),
            'lists' => $result->categories,
            'paginator' => $paginator,
            'breadcrumb' => $renderer->admin_breadcrumb(),
            'parent' => $parent,
        ]);
    }

    /**
     * Helps to render the category form for add/edit actions
     * @param Category $category
     * @param CategoryForm $form
     * @return JsonModel
     */
    protected function renderCategData(Category $category, CategoryForm $form)
    {
        $action = $category->getId() ? 'edit' : 'add';
        $parentId = (int)$category->getParentId();
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        $form->get('parent_id')->setValueOptions($categoryTree->getSelectOptions($category));
        $form->get('parent_id')->setValue($parentId);

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
            'parent' => $parentId,
        ));
    }

    public function get($id)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        /** @var CategoryTree $categoryTree */
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        /** @var Language $languagesService */
        $languagesService = $this->getServiceLocator()->get('language');
        
        $logic = new CategoryUpdate($entityManager, new Translator($this->getTranslator()), $categoryTree, $languagesService);
        $result = $logic->get($id);

        if($result->status !== StatusCodes::SUCCESS){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
                'parent' => 0,
            ]);
        }
        $category = $result->get('category');
        return $this->renderCategData($category, $result->get('form'));
    }

    public function update($id, $data)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        /** @var CategoryTree $categoryTree */
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        /** @var Language $languagesService */
        $languagesService = $this->getServiceLocator()->get('language');

        $logic = new CategoryUpdate($entityManager, new Translator($this->getTranslator()), $categoryTree, $languagesService);
        $result = $logic->update($id, $data);

        if($result->status === CategoryUpdate::ERR_CATEGORY_NOT_FOUND
            || $result->status === StatusCodes::ERR_INVALID_PARAM){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
                'parent' => 0,
            ]);
        }

        if($result->status === StatusCodes::SUCCESS){
            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $result->message],
                'parent' => $result->get('parent'),
            ]);
        }
        $category = $result->get('category');
        return $this->renderCategData($category, $result->get('form'));
    }

    /**
     * Show the Add Category form
     * @return JsonModel
     */
    public function addJsonAction()
    {
        $parentCategoryID = $this->params()->fromQuery('parent', null);

        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity-manager');
        /** @var CategoryTree $catTree */
        $catTree = $this->getServiceLocator()->get('category-tree');
        /** @var Language $languagesService */
        $languagesService = $this->getServiceLocator()->get('language');

        $logic = new CategoryCreate($em, new Translator($this->getTranslator()), $catTree, $languagesService);

        $result = $logic->showForm((int)$parentCategoryID);

        if($result->status === StatusCodes::ERR_INVALID_PARAM){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
                'parent' => $parentCategoryID
            ]);
        }

        //check if there is an existing language before entering new category
        if($result->status === CategoryCreate::ERR_NO_LANG){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
                'parent' => $parentCategoryID
            ]);
        }

        return $this->renderCategData($result->get('category'), $result->get('form'));
    }

    public function create($data)
    {
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity-manager');
        /** @var CategoryTree $categoryTree */
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        /** @var Language $languagesService */
        $languagesService = $this->getServiceLocator()->get('language');

        $logic = new CategoryCreate($em, new Translator($this->getTranslator()), $categoryTree, $languagesService);
        $result = $logic->create($data);

        if($result->status === StatusCodes::ERR_INVALID_PARAM || $result->status === CategoryCreate::ERR_NO_LANG){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
                'parent' => $data['parent_id']
            ]);
        }

        if($result->status === StatusCodes::SUCCESS){
            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $result->message],
                'parent' => (int)$data['parent_id'],
            ]);
        }

        return $this->renderCategData($result->category, $result->form);
    }

    public function delete($id)
    {
        $serviceLocator = $this->getServiceLocator();
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get('entity-manager');
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-core-dir'];
        //v_todo - delete cache file in data/cache if cache enabled in module Application/config/module.config.php

        $logic = new CategoryDelete($entityManager, new Translator($this->getTranslator()));
        $result = $logic->delete($id, $imgDir);
        
        if($result->status !== StatusCodes::SUCCESS){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
                'parent' => 0
            ]);
        }
        
        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $result->message],
            'parent' => $result->parent,
        ]);
    }
}
