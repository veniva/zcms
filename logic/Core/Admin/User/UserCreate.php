<?php

namespace Logic\Core\Admin\User;

use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;

class UserCreate extends UserBase
{
    public function showForm()
    {
        $user = new User();
        $this->form->bind($user);
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form,
            'user' => $user
        ]);
    }
}