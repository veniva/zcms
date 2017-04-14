<?php

namespace Logic\Core\Admin\User;

use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;

class UserUpdate extends UserBase
{
    const ERR_INSUFFICIENT_PRIVILEGES = 'uu.insufficient-privileges';

    public function showForm(int $id)
    {
        if ($id < 1) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        $user = $this->em->find(User::class, $id);
        if (!$user) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }

        //security check - is the edited user really having a role equal or less privileged than the editing user
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
}