<?php

namespace Logic\Core\Admin\User;

use Doctrine\ORM\EntityManager;
use Veniva\Lbs\Adapters\Interfaces\ITranslator;
use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Zend\Crypt\Password\PasswordInterface;
use Logic\Core\Admin\Form\User as UserForm;

class UserCreate extends UserBase
{
    /** @var User */
    protected $user;
    
    public function __construct(ITranslator $translator, EntityManager $em, User $loggedInUser, UserForm $form = null)
    {
        parent::__construct($translator, $em, $loggedInUser, $form);
        
        $this->user = new User();
    }

    public function showForm()
    {
        $user = $this->user;
        $this->form->bind($user);
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form,
            'user' => $user
        ]);
    }
    
    public function create(array $data, PasswordInterface $passwordAdapter)
    {
        $user = $this->user;
        $user->setPasswordAdapter($passwordAdapter);
        
        $form = $this->form;
        $form->bind($user);
        $form->setData($data);
        
        if ($form->isValid()) {
            return $this->createUser($user, $form);
        }
        
        return $this->result(StatusCodes::ERR_INVALID_FORM, StatusMessages::ERR_INVALID_FORM_MSG, [
            'form' => $this->form,
            'user' => $user
        ]);
    }

    /**
     * Insert user when the form is valid
     * @param User $user
     * @param UserForm $form
     * @return \Veniva\Lbs\Result
     */
    public function createUser(User $user, UserForm $form)
    {
        //security check - is the new role is equal or less privileged than the editing user's role
        $newRole = $form->getData()->getRole();
        if (!$this->loggedInUser->canEdit($newRole))
            return $this->result(parent::ERR_NO_RIGHT_ASSIGN_ROLE, parent::ERR_NO_RIGHT_ASSIGN_ROLE_MSG);

        $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
        $user->setRegDate();

        $this->em->persist($user);
        $this->em->flush();

        return $this->result(StatusCodes::SUCCESS, 'The user has been inserted successfully');
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}