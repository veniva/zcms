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
use Logic\Core\Admin\Form\User as UserForm;
use Logic\Core\Admin\User\UserCreate;
use Logic\Core\Admin\User\UserDelete;
use Logic\Core\Admin\User\UserList;
use Logic\Core\Admin\User\UserUpdate;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Crypt\Password\PasswordInterface;

class UserController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

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
        $pageNumber = $this->params()->fromQuery('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $translator = $this->getTranslator();
        
        $logic = new UserList(new Translator($translator), $entityManager);
        $result = $logic->showList($pageNumber);
        $usersPaginated = $result->get('users_paginated');

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $paginator = $renderer->paginationControl($usersPaginated, 'Sliding', 'paginator/sliding_ajax');

        $auth = $this->getServiceLocator()->get('auth');
        return new JsonModel([
            'title' => $result->get('title'),
            'lists' => $result->get('user_data'),
            'paginator' => $paginator,
            'identity_id' => $auth->getIdentity()->getId()
        ]);
    }

    public function get($id)
    {
        $this->dependencyProvider($translator, $em, $loggedInUser);
        $logic = new UserUpdate(new Translator($translator), $em, $loggedInUser);

        $result = $logic->showForm($id);
        if ($result->status !== StatusCodes::SUCCESS) {
            return $this->redirToList($result->message, 'error');
        }

        return $this->renderData('edit', $result->get('form'), $result->get('edit_own'), $result->get('user'));
    }

    public function addJsonAction()
    {
        $this->dependencyProvider($translator, $em, $loggedInUser);

        $logic = new UserCreate(new Translator($translator), $em, $loggedInUser);
        $result = $logic->showForm();

        return $this->renderData('add', $result->get('form'), false, $result->get('user'));
    }

    protected function dependencyProvider(&$translator, &$em, &$loggedInUser)
    {
        $translator = $this->getTranslator();
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity-manager');
        /** @var User $loggedInUser */
        $loggedInUser = $this->getServiceLocator()->get('current-user');
    }

    protected function renderData($action, UserForm $form, $editOwn, User $user)
    {
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $viewModel = new ViewModel([
            'action' => $action,
            'id' => $user->getId(),
            'form' => $form,
            'editOwn' => $editOwn,
            'user' => $user,
        ]);
        $viewModel->setTemplate('admin/user/edit');

        return new JsonModel([
            'title' => $this->translator->translate(ucfirst($action).' a user'),
            'form' => $renderer->render($viewModel),
        ]);
    }

    public function update($id, $data)
    {
        $this->dependencyProvider($translator, $em, $loggedInUser);
        $logic = new UserUpdate(new Translator($translator), $em, $loggedInUser);

        $result = $logic->update($id, $data);
        if ($result->status !== StatusCodes::ERR_INVALID_FORM && $result->status !== StatusCodes::SUCCESS) {
            return $this->redirToList($result->message, 'error');
        } elseif ($result->status === StatusCodes::ERR_INVALID_FORM) {
            return $this->renderData('edit', $result->get('form'), $result->get('edit_own'), $result->get('user'));
        }

        return $this->redirToList($result->message);
    }

    public function create($data)
    {
        $this->dependencyProvider($translator, $em, $loggedInUser);
        $logic = new UserCreate(new Translator($translator), $em, $loggedInUser);

        /** @var PasswordInterface $passwordAdapter */
        $passwordAdapter = $this->getServiceLocator()->get('password-adapter');
        $result = $logic->create($data, $passwordAdapter);

        if ($result->status !== StatusCodes::ERR_INVALID_FORM && $result->status !== StatusCodes::SUCCESS) {
            return $this->redirToList($result->message, 'error');
        } elseif ($result->status === StatusCodes::ERR_INVALID_FORM) {
            return $this->renderData('add', $result->get('form'), $result->get('edit_own'), $result->get('user'));
        }

        $this->getResponse()->setStatusCode(201);

        return $this->redirToList($result->message);
    }

    public function delete($id)
    {
        $this->dependencyProvider($translator, $em, $loggedInUser);
        $logic = new UserDelete(new Translator($translator), $em, $loggedInUser);

        $result = $logic->delete($id);
        if ($result->status !== StatusCodes::SUCCESS) {
            return $this->redirToList($result->message, 'error');
        }

        return $this->redirToList($result->message);
    }

    protected function redirToList($message = null, $messageType = 'success')
    {
        if(!in_array($messageType, ['success', 'error', 'info']))
            throw new \InvalidArgumentException('Un-existing message type');

        return new JsonModel([
            'message' => ['type' => $messageType, 'text' => $this->translator->translate($message)],
        ]);
    }
}