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
        $translator = $this->getTranslator();
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity-manager');
        /** @var User $loggedInUser */
        $loggedInUser = $this->getServiceLocator()->get('current-user');

        $logic = new UserUpdate(new Translator($translator), $em, $loggedInUser);

        $result = $logic->showForm($id);
        if ($result->status !== StatusCodes::SUCCESS) {
            return $this->redirToList($result->message, 'error');
        }

        return $this->renderData('edit', $result->get('form'), $result->get('edit-own'), $result->get('user'));
    }

    public function addJsonAction()
    {
        return $this->addEditUser();
    }

    /**
     * Displays the form
     * @param $id NULL - add ELSE edit
     * @return JsonModel
     */
    public function addEditUser($id = null)
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $user = $this->getServiceLocator()->get('user-entity');//accessed it from service manager as this way the User::setPasswordAdapter() is initialized
        if($id){
            $user = $entityManager->find(get_class($user), $id);
            if(!$user)
                return $this->redirMissingUser($id);
        }

        $loggedInUser = $this->getServiceLocator()->get('current-user');
        $editOwn = $loggedInUser->getId() == $user->getId();
        //security check - is the edited user really having a role equal or less privileged to the editing user
        if(!$loggedInUser->canEdit($user->getRole())){
            $this->getResponse()->setStatusCode(403);
            return $this->redirToList('You have no right to edit this user', 'error');
        }

        $action = $id ? 'edit' : 'add';
        $form = new UserForm($loggedInUser, $this->getServiceLocator()->get('entity-manager'));
        $form->bind($user);

        return $this->renderData($action, $form, $editOwn, $user);
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
        return $this->handleCreateUpdate($data, $id);
    }

    public function create($data)
    {
        return $this->handleCreateUpdate($data);
    }

    public function handleCreateUpdate($data, $id = null)
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $user = $this->getServiceLocator()->get('user-entity');//accessed it from service manager as this way the User::setPasswordAdapter() is initialized
        if($id){
            $user = $entityManager->find(get_class($user), $id);
            if(!$user)
                return $this->redirMissingUser($id);
        }

        $loggedInUser = $this->getServiceLocator()->get('current-user');
        $editOwn = $loggedInUser->getId() == $user->getId();
        //security check - is the edited user really having a role equal or less privileged to the editing user
        if(!$loggedInUser->canEdit($user->getRole()))
            return $this->redirToList('You have no right to edit this user', 'error');

        $currentUserName = $user->getUname();
        $currentEmail = $user->getEmail();
        $form = new UserForm($loggedInUser, $this->getServiceLocator()->get('entity-manager'));
        $form->bind($user);

        $form->setData($data);
        $action = $id ? 'edit' : 'add';
        if($form->isValid($action, $currentUserName, $currentEmail, $editOwn)){
            //security check - is the new role equal or less privileged to the editing user
            $newRole = $form->getData()->getRole();
            if(!$loggedInUser->canEdit($newRole))//this protection is redundant as there will be notFoundInTheHaystack validation error
                return $this->redirToList('You have no right to assign this user role', 'error');

            if($editOwn && isset($data['role']))
                return $this->redirToList('You have no right to assign new role to yourself', 'error');

            $newPassword = $form->getInputFilter()->get('password_fields')->get('password')->getValue();
            if($newPassword)
                $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
            $user->setRegDate();
            $entityManager->persist($user);
            $entityManager->flush();

            if($this->getRequest()->isPost()){
                $this->getResponse()->setStatusCode(201);
            }

            return $this->redirToList('The user has been '.$action.'ed successfully');
        }

        return $this->renderData($action, $form, $editOwn, $user);
    }

    public function delete($id)
    {
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        $user = $entityManager->find(get_class(new User), $id);
        if(!$user instanceof User){
            return $this->redirMissingUser($id);
        }
        //make sure that the user cannot delete his own profile
        $loggedInUser = $this->getServiceLocator()->get('current-user');
        if($loggedInUser->getId() == $user->getId()){
            $this->redirToList('You cannot delete your own profile', 'error');
        }

        $entityManager->remove($user);//contained listings are cascade removed from the ORM!!
        $entityManager->flush();

        return $this->redirToList('The user was removed successfully');
    }

    protected function redirMissingUser($id)
    {
        return $this->redirToList('There is no user with id = '.$id, 'error');
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