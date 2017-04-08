<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Logic\Core\Adapters\Zend\Translator;
use Logic\Core\Admin\Language\LanguageCreate;
use Logic\Core\Admin\Language\LanguageDelete;
use Logic\Core\Admin\Language\LanguageList;
use Logic\Core\Admin\Language\LanguageUpdate;
use Logic\Core\Form\Language as LanguageForm;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Result;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class LanguageController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    const ACTION_EDIT = 'edit';
    const ACTION_ADD = 'add';
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    protected $flagsDir = '/img/flags/';

    public function listAction()
    {
        return new ViewModel();
    }

    public function getList()
    {
        $pageNumber = $this->params()->fromQuery('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');

        $logic = new LanguageList(new Translator($this->getTranslator()), $entityManager);
        $result = $logic->getList($pageNumber);

        $renderer = $this->getServiceLocator()->get('ViewRenderer');
        $paginator = $renderer->paginationControl($result->get('langs_paginated'), 'Sliding', 'paginator/sliding_ajax');

        return new JsonModel([
            'title' => $result->get('title'),
            'lists' => $result->get('lang_data'),
            'paginator' => $paginator
        ]);
    }

    public function get($id)
    {
        $this->dependencyProvider($translator, $em, $flagCodes);
        $logic = new LanguageUpdate($translator, $em, $flagCodes);

        $result = $logic->showForm($id);
        if($result->status !== StatusCodes::SUCCESS) {
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
            ]);
        }

        return $this->renderData('edit', $result->get('language'), $result->get('form'));
    }

    public function addJsonAction()
    {
        $this->dependencyProvider($translator, $em, $flagCodes);
        $logic = new LanguageCreate($translator, $em, $flagCodes);

        $result = $logic->showForm();
        return $this->renderData('add', $result->get('language'), $result->get('form'));
    }

    protected function renderData($action, $language, $form)
    {
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $viewModel = new ViewModel([
            'action' => $action,
            'id' => $language->getId(),
            'form' => $form,
            'lang' => !empty($language->getIsoCode()) ? $language : null,
            'flagCode' => $this->getRequest()->isPost() ? $this->params()->fromPost('isoCode') :
                $language->getIsoCode() ?: null
        ]);
        $viewModel->setTemplate('admin/language/edit');

        return new JsonModel([
            'title' => $this->translator->translate(ucfirst($action).' a language'),
            'form' => $renderer->render($viewModel),
        ]);
    }

    public function update($id, $data)
    {
        $this->dependencyProvider($translator, $em, $flagCodes);
        $logic = new LanguageUpdate($translator, $em, $flagCodes);
        $result = $logic->update($id, $data);

        return $this->handleResult($result, self::ACTION_EDIT);
    }

    public function create($data)
    {
        $this->dependencyProvider($translator, $em, $flagCodes);
        $logic = new LanguageCreate($translator, $em, $flagCodes);
        $result = $logic->create($data);

        return $this->handleResult($result, self::ACTION_ADD);
    }

    public function dependencyProvider(&$translator, &$em, &$flagCodes = false)
    {
        $translator = new Translator($this->getTranslator());
        $em = $this->getServiceLocator()->get('entity-manager');
        if($flagCodes !== false)
            $flagCodes = $this->getServiceLocator()->get('flag-codes');
    }

    public function handleResult(Result $result, string $action)
    {
        if($action !== self::ACTION_ADD && $action !== self::ACTION_EDIT) throw new \InvalidArgumentException();

        if($result->status !== StatusCodes::SUCCESS && $result->status !== StatusCodes::ERR_INVALID_FORM) {
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
            ]);

        } elseif ($result->status === StatusCodes::ERR_INVALID_FORM) {
            return $this->renderData($action, $result->get('language'), $result->get('form'));

        } else {
            if($action === self::ACTION_ADD) $this->getResponse()->setStatusCode(201);

            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $result->message],
            ]);
        }
    }

    public function delete($id)
    {
        $this->dependencyProvider($translator, $em);
        $logic = new LanguageDelete($translator, $em);
        $result = $logic->delete($id);

        if ($result->status !== StatusCodes::SUCCESS) {
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message]
            ]);
        }

        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $result->message]
        ]);

    }
}