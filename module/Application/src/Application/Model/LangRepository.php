<?php

namespace Application\Model;


use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator;

class LangRepository extends EntityRepository
{
    public function getActiveLangs()
    {
        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder->where("l.status > 0");
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getLanguagesPaginated()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l');
        return new Paginator(new \Application\Paginator\DoctrineAdapter($qb->getQuery()));
    }
}