<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

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
        $qb->select('l')->orderBy('l.status', 'desc')->addOrderBy('l.id');
        return new Paginator(new \Application\Paginator\DoctrineAdapter($qb->getQuery()));
    }
}