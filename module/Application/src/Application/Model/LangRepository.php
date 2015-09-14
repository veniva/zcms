<?php

namespace Application\Model;


use Doctrine\ORM\EntityRepository;

class LangRepository extends EntityRepository
{
    public function allFrontendActiveLangs()
    {
        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder->where("l.frontEnd > 0");
        return $queryBuilder->getQuery()->getArrayResult();
    }
}