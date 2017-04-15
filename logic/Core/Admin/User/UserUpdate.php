<?php

namespace Logic\Core\Admin\User;

use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Logic\Core\Admin\Form\User as UserForm;
use Logic\Core\Result;

class UserUpdate extends UserBase
{
    const ERR_INSUFFICIENT_PRIVILEGES = 'uu.insufficient-privileges';
    const ERR_NO_RIGHT_ASSIGN_ROLE = 'uu.no_right_assign_role';
    const ERR_SELF_NEW_ROLE = 'uu.err_self_new_role';

    public function showForm(int $id): Result
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
    
    public function update(int $id, array $data): Result
    {
        $result = $this->showForm($id);
        if ($result->status != StatusCodes::SUCCESS) {
            return $result;
        }

        $form = $this->form;
        $form->setData($data);
        $user = $result->get('user');
        $editOwn = $result->get('edit_own');

        if ($form->isValid('edit', $user->getUname(), $user->getEmail(), $editOwn)) {
            $result = $this->updateUser($user, $form, $data, $editOwn);
            return $result;
        }
        
        return $this->result(StatusCodes::ERR_INVALID_FORM, StatusMessages::ERR_INVALID_FORM_MSG, [
            'form' => $form,
            'edit_own' => $editOwn,
            'user' => $user
        ]);
    }
    
    public function updateUser(User $user, UserForm $form, array $data, bool $editOwn): Result
    {
        //security checks
        $newRole = $form->getData()->getRole();
        if (!$this->loggedInUser->canEdit($newRole)) {
            return $this->result(self::ERR_NO_RIGHT_ASSIGN_ROLE, 'You have no right to assign this user role');
        }

        if ($editOwn && isset($data['role'])) {
            return $this->result(self::ERR_SELF_NEW_ROLE, 'You have no right to assign new role to yourself');
        }

        $newPassword = $form->getInputFilter()->get('password_fields')->get('password')->getValue();
        if ($newPassword) {
            $user->setUpass($newPassword);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->result(StatusCodes::SUCCESS, 'The user has been edited successfully');
    }
}