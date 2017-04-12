<?php

namespace Logic\Core\Admin\User;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Logic\Core\Admin\Form\User as UserForm;

class UserUpdate extends BaseLogic
{
    const ERR_INSUFFICIENT_PRIVILEGES = 'uu.insufficient-privileges';

    /** @var EntityManager */
    protected $em;
    /** @var User */
    protected $loggedInUser;

    protected $form;
    
    public function __construct(ITranslator $translator, EntityManager $em, User $loggedInUser, UserForm $form = null)
    {
        parent::__construct($translator);
        
        $this->em = $em;
        $this->loggedInUser = $loggedInUser;
        
        $this->form = $form ?: new UserForm($loggedInUser, $em);
    }

    public function showForm(int $id)
    {
        if ($id < 1) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        $user = $this->em->find(User::class, $id);
        if (!$user) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }

        //security check - is the edited user really having a role equal or less privileged to the editing user
        if (!$this->loggedInUser->canEdit($user->getRole())) {
            return $this->result(self::ERR_INSUFFICIENT_PRIVILEGES, 'You have no right to edit this user');
        }

        $this->form->bind($user);
        $editOwn = $this->loggedInUser->getId() == $user->getId();
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form,
            'edit_own' => $editOwn,
            'user' => $user
        ]);
    }

    /**
     * @return UserForm|null
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param UserForm|null $form
     * @return UserUpdate
     */
    public function setForm($form): UserUpdate
    {
        $this->form = $form;
        return $this;
    }
}