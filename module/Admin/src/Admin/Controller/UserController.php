<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Application\Model\Entity\User;
use Zend\Authentication\AuthenticationService;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function listAction()
    {
        return new ViewModel();
    }

    public function getList()
    {
        $pageNumber = $this->params()->fromQuery('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $userRepo = $entityManager->getRepository(get_class(new User()));

        $usersPaginated = $userRepo->getUsersPaginated();
        $usersPaginated->setCurrentPageNumber($pageNumber);

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $paginator = $renderer->paginationControl($usersPaginated, 'Sliding', 'paginator/sliding_ajax');

        $i = 0;
        $userData = [];
        foreach($usersPaginated as $user){
            $userData[$i]['id'] = $user->getId();
            $userData[$i]['uname'] = $user->getUname();
            $userData[$i]['email'] = $user->getEmail();
            $userData[$i]['role'] = $user->getRoleName();
            $userData[$i]['reg_date'] = $user->getRegDate('m-d-Y');
            $i++;
        }

        $auth = new AuthenticationService();
        return new JsonModel([
            'title' => $this->getTranslator()->translate('Users'),
            'lists' => $userData,
            'paginator' => $paginator,
            'various' => ['identity_id' => $auth->getIdentity()->getId()]
        ]);
    }

    public function editJsonAction(){
        $id = $this->params()->fromQuery('id', null);
        if(empty($id)){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('There was missing/wrong parameter in the request')],
            ]);
        }

        return $this->addEditUser($id);
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
        if(!$loggedInUser->canEdit($user->getRole()))
            return $this->redirToList('You have no right to edit this user', 'error');

        $action = $id ? 'edit' : 'add';
        $form = new \Admin\Form\User($loggedInUser, $this->getServiceLocator()->get('entity-manager'));
        $form->bind($user);

        return $this->renderData($action, $form, $editOwn, $user);
    }

    protected function renderData($action, \Admin\Form\User $form, $editOwn, User $user)
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

    public function update($id)
    {
        return $this->handleCreateUpdate($id);
    }

    public function create()
    {
        return $this->handleCreateUpdate();
    }

    public function handleCreateUpdate($id = null)
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
        $form = new \Admin\Form\User($loggedInUser, $this->getServiceLocator()->get('entity-manager'));
        $form->bind($user);

        $request = $this->getRequest();
        if($request->isPost()){
            $data = $request->getPost();
        }else{//is PUT
            $data = [];
            parse_str($request->getContent(), $data);
        }

        $form->setData($data);
        $action = $id ? 'edit' : 'add';
        if($form->isValid($action, $currentUserName, $currentEmail, $editOwn)){
            //security check - is the new role equal or less privileged to the editing user
            $newRole = $form->getData()->getRole();
            if(!$loggedInUser->canEdit($newRole))
                return $this->redirToList('You have no right to assign this user role', 'error');

            if($editOwn && $request->getPost()['role'])
                return $this->redirToList('You have no right to assign new role to yourself', 'error');

            $newPassword = $form->getInputFilter()->get('password_fields')->get('password')->getValue();
            if($newPassword)
                $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
            $user->setRegDate();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirToList('The user has been '.$action.'ed successfully');
        }

        return $this->renderData($action, $form, $editOwn, $user);
    }

    public function delete()
    {
        $id = $this->params()->fromRoute('id', null);
        if(empty($id)){
            return $this->redirMissingUser($id);
        }

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        $user = $entityManager->find(get_class(new User), $id);
        if(!$user instanceof User){
            return $this->redirMissingUser($id);
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