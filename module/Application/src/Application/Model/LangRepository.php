<?php

namespace Application\Model;


use Doctrine\ORM\EntityRepository;

class LangRepository extends EntityRepository
{
    public function getActiveLangs()
    {
        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder->where("l.status > 0");
        return $queryBuilder->getQuery()->getArrayResult();
    }
}