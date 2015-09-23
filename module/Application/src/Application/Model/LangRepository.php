<?php

namespace Application\Model;


use Doctrine\ORM\EntityRepository;

class LangRepository extends EntityRepository
{
    public function allFrontendActiveLangs($space = 'frontEnd')
    {
        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder->where("l.$space > 0");
        return $queryBuilder->getQuery()->getArrayResult();
    }
}