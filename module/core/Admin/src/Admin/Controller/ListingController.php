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
use Logic\Core\Admin\Page\PageCreate;
use Logic\Core\Admin\Page\PageDelete;
use Logic\Core\Admin\Page\PageList;
use Logic\Core\Admin\Page\PageUpdate;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Services\CategoryTree;
use Logic\Core\Services\Language;
use Zend\Form\Element\Select;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Form\Element;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ListingController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;
    
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    /**
     * Initially loaded to display the list page's template
     * @return ViewModel
     */
    public function listAction()
    {
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        $selectCategoryElement = new Select('filter_category');
        $selectCategoryElement->setAttribute('id', 'filter_category');
        $selectCategoryElement->setEmptyOption($this->translator->translate('All categories'));
        $selectCategoryElement->setValueOptions($categoryTree->getSelectOptions());

        return new ViewModel([
            'selectCategory' => $selectCategoryElement,
            'locale' => $this->translator->getLocale()
        ]);
    }

    /**
     * Called asynchronously
     * @return JsonModel
     */
    public function getList()
    {
        $parentCategory = $this->params()->fromQuery('filter', '0');
        $defaultLanguageID = $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId();
        $page = $this->params()->fromQuery('page', 1);

        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity-manager');

        $logic = new PageList($em, new Translator($this->getTranslator()));
        $result = $logic->showList($defaultLanguageID, $parentCategory, $page);

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $paginator = $renderer->paginationControl($result->get('pages_paginated'), 'Sliding', 'paginator/sliding_ajax', ['id' => $parentCategory]);

        return new JsonModel([
            'title' => $this->getTranslator()->translate('Pages'),
            'lists' => $result->get('pages'),
            'paginator' => $paginator,
            'parentCategory' => $parentCategory,
            'defaultLanguageID' => $defaultLanguageID,
        ]);
    }

    public function get($id)
    {
        $parentFilter = $this->params()->fromQuery('filter', 0);
        /** @var EntityManager $em */
        $em = null;
        /** @var CategoryTree $ct */
        $ct = null;
        /** @var Language $language */
        $language = null;
        $this->dp($em, $ct, $language);
        
        $logic = new PageUpdate(new Translator($this->getTranslator()), $em, $ct, $language);
        $result = $logic->showForm($id, $parentFilter);

        return $this->renderData($result->get('form'), $result->get('page'), 'edit', $result->get('active_languages'));
    }

    public function addJsonAction()
    {
        $parentFilter = $this->params()->fromQuery('filter', 0);
        /** @var EntityManager $em */
        $em = null;
        /** @var CategoryTree $ct */
        $ct = null;
        /** @var Language $language */
        $language = null;
        $this->dp($em, $ct, $language);

        $logic = new PageCreate(new Translator($this->getTranslator()), $em, $ct, $language);
        $result = $logic->showForm($parentFilter);

        if($result->status === PageCreate::ERR_NO_CATEGORY)
            return $this->redirectToList(PageCreate::ERR_NO_CATEGORY_MSG, 'error');

        return $this->renderData($result->get('form'), $result->get('page'), 'add', $result->get('active_languages'));
    }

    private function dp(EntityManager &$em = null, CategoryTree &$ct = null, Language &$language = null)
    {
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity-manager');
        /** @var CategoryTree $ct */
        $ct = $this->getServiceLocator()->get('category-tree');
        /** @var Language $language */
        $language = $this->getServiceLocator()->get('language');
    }

    protected function renderData($form, $listing, $action, $languages, $message = null)
    {
        //add form-control CSS class to some form elements
        foreach($form->getFieldsets() as $fieldset){
            foreach($fieldset->getFieldsets() as $subFieldset){
                foreach($subFieldset->getElements() as $element){
                    $inputCSSClass = !empty($element->getAttribute('class')) ? $element->getAttribute('class').' ' : '';
                    $element->setAttribute('class', $inputCSSClass.'form-control');
                }
            }
        }

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $viewModel = new ViewModel([
            'id' => $listing->getId(),
            'form' => $form,
            'listing' => $listing,
            'action' => $action,
            'image' => $listing->getListingImage() ? $listing->getId().'/'.$listing->getListingImage()->getImageName() : null,
            'locale' => $this->translator->getLocale(),
            'activeLanguages' => $languages,
        ]);
        $viewModel->setTemplate('admin/listing/edit');
        $jsonModel = [
            'title' => $this->translator->translate(ucfirst($action).' a page'),
            'form' => $renderer->render($viewModel),
        ];
        if($message)
            $jsonModel['message'] = $message;

        return new JsonModel($jsonModel);
    }

    public function update($id, $data)
    {
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-core-dir'];
        $translator = new Translator($this->getServiceLocator()->get('translator'));
        $this->dp($em, $ct, $language);

        $logic = new PageUpdate($translator, $em, $ct, $language);
        $result = $logic->update($id, $data, $imgDir);

        if($result->status === StatusCodes::ERR_INVALID_PARAM){
            return $this->redirectToList($result->message, 'error');

        }else if($result->status === StatusCodes::ERR_INVALID_FORM){
            $message = ['type' => 'error', 'text' => $result->message, 'no_redir' => 1];
            return $this->renderData($result->get('form'), $result->get('page'), 'edit', $language->getActiveLanguages(), $message);

        }

        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $result->message],
        ]);
        //v_todo - delete cache file in data/cache if cache enabled in module Application/config/module.config.php
    }

    public function create($data)
    {
        $translator = new Translator($this->getServiceLocator()->get('translator'));
        $this->dp($em, $ct, $language);
        $logic = new PageCreate($translator, $em, $ct, $language);

        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-core-dir'];
        $result = $logic->create($data, $imgDir);

        if($result->status === PageCreate::ERR_NO_CATEGORY){
            return $this->redirectToList($result->message, 'error');

        }else if($result->status === StatusCodes::ERR_INVALID_FORM){
            $message = ['type' => 'error', 'text' => $result->message, 'no_redir' => 1];
            return $this->renderData($result->get('form'), $result->get('page'), 'add', $language->getActiveLanguages(), $message);
        }

        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $result->message]
        ]);
    }

    public function deleteAjaxAction()
    {
        $ids = $this->params()->fromPost('ids', null);
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-core-dir'];
        
        $this->dp($em, $ct, $language);
        $translator = new Translator($this->getTranslator());
        $logic = new PageDelete($translator, $em, $ct, $language);
        
        $result = $logic->delete($imgDir, $ids);
        
        if($result->status !== StatusCodes::SUCCESS){
            return $this->redirectToList($result->message, 'error');
        }

        return $this->redirectToList($result->message);
    }

    protected function redirectToList($message = null, $messageType = 'success')
    {
        if(!in_array($messageType, ['success', 'error', 'info']))
            throw new \InvalidArgumentException('Un-existing message type');

        return new JsonModel([
            'message' => ['type' => $messageType, 'text' => $message],
        ]);
    }
}
