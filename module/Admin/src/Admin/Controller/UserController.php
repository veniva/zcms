<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Application\Model\Entity\User;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function indexAction()
    {
        $pageNumber = $this->params()->fromRoute('page');
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $userRepo = $entityManager->getRepository(get_class(new User()));

        $usersPaginated = $userRepo->getUsersPaginated();
        $usersPaginated->setCurrentPageNumber($pageNumber);

        return [
            'page' => $pageNumber,
            'users' => $usersPaginated,
        ];
    }

    public function editAction(){
        $id = $this->params()->fromRoute('id', null);
        $page = $this->params()->fromRoute('page');
        if(!$id)
            return $this->redir()->toRoute('admin/default', ['controller' => 'user', 'page' => $page]);

        return $this->addEditUser($id, $page);
    }

    public function addAction()
    {
        $page = $this->params()->fromRoute('page');

        $return = $this->addEditUser(null, $page);
        if($return instanceof ViewModel) {
            $return->setTemplate('admin/user/edit');
        }
        return $return;
    }

    public function addEditUser($id, $page)
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $user = $this->getServiceLocator()->get('user-entity');//accessed it from service manager as this way the User::setPasswordAdapter() is initialized
        if($id){
            $user = $entityManager->find(get_class($user), $id);
            if(!$user)
                return $this->redirMissingUser($id, $page);
        }

        $loggedInUser = $this->getServiceLocator()->get('current-user');
        $editOwn = $loggedInUser->getId() == $user->getId();
        //security check - is the edited user really having a role equal or less privileged to the editing user
        if(!$loggedInUser->canEdit($user->getRole()))
            return $this->redirToList($page, 'You have no right to edit this user', 'error');

        $action = $id ? 'edit' : 'add';
        $currentUserName = $user->getUname();
        $currentEmail = $user->getEmail();
        $form = new \Admin\Form\User($loggedInUser, $this->getServiceLocator()->get('entity-manager'));
        $form->bind($user);

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid($action, $currentUserName, $currentEmail, $editOwn)){
                //security check - is the new role equal or less privileged to the editing user
                $newRole = $form->getData()->getRole();
                if(!$loggedInUser->canEdit($newRole))
                    return $this->redirToList($page, 'You have no right to assign this user role', 'error');

                if($editOwn && $request->getPost()['role'])
                    return $this->redirToList($page, 'You have no right to assign new role to yourself', 'error');

                $newPassword = $form->getInputFilter()->get('password_fields')->get('password')->getValue();
                if($newPassword)
                    $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
                $user->setRegDate();
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirToList($page, 'The user has been '.$action.'ed successfully');
            }else{
                $this->flashMessenger()->addErrorMessage($this->translator->translate("Please check the form for errors"));
            }
        }

        return new ViewModel([
            'action' => $action,
            'page' => $page,
            'form' => $form,
            'editOwn' => $editOwn,
            'user' => $user,
            'account_settings' => $this->params()->fromQuery('account_settings', false),
        ]);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $page = $this->params()->fromRoute('page');
        if(empty($id)){
            return $this->redirMissingUser($id, $page);
        }

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');

        $user = $entityManager->find(get_class(new User), $id);
        if(!$user instanceof User){
            return $this->redirMissingUser($id, $page);
        }
        $entityManager->remove($user);//contained listings are cascade removed from the ORM!!
        $entityManager->flush();

        return $this->redirToList($page, 'The user was removed successfully');
    }

    protected function redirMissingUser($id, $page)
    {
        return $this->redirToList($page, 'There is no user with id = '.$id, 'error');
    }

    protected function redirToList($page = null, $message = null, $messageType = 'success')
    {
        if(!in_array($messageType, ['success', 'error', 'info']))
            throw new \InvalidArgumentException('Un-existing message type');

        if($message)
            $this->flashMessenger()->addMessage($this->translator->translate($message), $messageType);

        return $this->redir()->toRoute('admin/default', [
            'controller' => 'user',
            'page' => $page,
        ]);
    }
}