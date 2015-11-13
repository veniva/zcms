<?php

namespace Application\Model;
use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator;

class UserRepository extends EntityRepository
{
    public function getUsersPaginated()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u');
        return new Paginator(new \Application\Paginator\DoctrineAdapter($qb->getQuery()));
    }
}