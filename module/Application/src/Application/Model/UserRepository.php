<?php

namespace Application\Model;
use Doctrine\ORM\EntityRepository;
use Zend\Authentication\AuthenticationService;
use Zend\Paginator\Paginator;

class UserRepository extends EntityRepository
{
    public function getUsersPaginated()
    {
        $auth = new AuthenticationService();
        $qb = $this->createQueryBuilder('u');
        $qb->select('u');
        if($auth->hasIdentity()){
            $qb->where('u.id != '.$auth->getIdentity()->getId());
            $qb->andWhere('u.role >='.$auth->getIdentity()->getRole());
        }
        return new Paginator(new \Application\Paginator\DoctrineAdapter($qb->getQuery()));
    }
}