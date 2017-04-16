<?php

namespace Logic\Core\Admin\User;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;

class UserDelete extends BaseLogic
{
    const ERR_CAN_NOT_DELETE_OWN_PROFILE = 'ud.cannot-delete-own-profile';
    const ERR_INSUFFICIENT_RIGHTS = 'ud.have-no-sufficient-rights';
    
    /** @var EntityManager */
    protected $em;
    /** @var User */
    protected $loggedInUser;
    
    public function __construct(ITranslator $translator, EntityManager $em, User $loggedInUser)
    {
        parent::__construct($translator);

        $this->em = $em;
        $this->loggedInUser = $loggedInUser;
    }

    public function delete(int $id)
    {
        if ($id < 1) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        /** @var User $user */
        $user = $this->em->find(User::class, $id);
        if (!$user) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        if ($this->loggedInUser->getId() == $user->getId()) {
            return $this->result(self::ERR_CAN_NOT_DELETE_OWN_PROFILE, 'You cannot delete your own profile');
        }
        
        //check the logged in user has right to delete that particular user account
        if (!$this->loggedInUser->canEdit($user->getRole())) {
            return $this->result(self::ERR_INSUFFICIENT_RIGHTS, 'You have insufficient user privileges to delete this user');
        }

        $this->em->remove($user);
        $this->em->flush();
        
        return $this->result(StatusCodes::SUCCESS, 'The user was removed successfully');
    }
}