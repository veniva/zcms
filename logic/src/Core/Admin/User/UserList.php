<?php

namespace Logic\Core\Admin\User;

use Doctrine\ORM\EntityManager;
use Veniva\Lbs\Adapters\Interfaces\ITranslator;
use Veniva\Lbs\BaseLogic;
use Veniva\Lbs\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Logic\Core\Model\UserRepository;

class UserList extends BaseLogic
{
    /** @var EntityManager */
    protected $em;
    /** @var User */
    protected $loggedInUser;
    
    public function __construct(ITranslator $translator, EntityManager $entityManager,  User $loggedInUser)
    {
        parent::__construct($translator);
        
        $this->em = $entityManager;
        $this->loggedInUser = $loggedInUser;
    }

    public function showList(int $pageNumber = 1)
    {
        /** @var UserRepository $usersRepo */
        $usersRepo = $this->em->getRepository(User::class);
        $usersPaginated = $usersRepo->getEditableUsersPaginated($this->loggedInUser->getId());
        $usersPaginated->setCurrentPageNumber($pageNumber);

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

        return $this->result(StatusCodes::SUCCESS, null, [
            'title' => $this->translator->translate('Users'),
            'users_paginated' => $usersPaginated,
            'user_data' => $userData
        ]);
    }
}