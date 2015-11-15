<?php

namespace Admin\Controller;


use Application\Model\Entity\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
{
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

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
        if($id){
            $userEntity = new User();
            $user = $entityManager->find(get_class($userEntity), $id);
            if(!$user)
                return $this->redirMissingUser($id, $page);
        }else{
            $user = $this->getServiceLocator()->get('user-entity');//accessed from service manager as this way the User::setPasswordAdapter() is initialized
        }
        $action = $id ? 'edit' : 'add';
        $currentUserName = $user->getUname();
        $currentEmail = $user->getEmail();
        $form = new \Admin\Form\User($this->getServiceLocator()->get('entity-manager'));
        $form->bind($user);

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid($action, $currentUserName, $currentEmail, get_class($user))){
                $newPassword = $form->getInputFilter()->get('password')->getValue();
                if($newPassword)
                    $user->setUpass($form->getInputFilter()->get('password')->getValue());
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
            return $this->redirToList($page, 'There is no user with id = '.$id, 'error');
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
        if(!in_array($messageType, ['success', 'error', 'info', 'default']))
            throw new \InvalidArgumentException('Un-existing message type');

        $this->flashMessenger()->{'add'.$messageType.'Message'}($this->translator->translate($message));
        return $this->redir()->toRoute('admin/default', [
            'controller' => 'user',
            'page' => $page,
        ]);
    }
}