<?php

namespace Application\Model;



use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator;

class ListingRepository extends EntityRepository
{
    public function getListingsPaginated($categoryId = null)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l, lc, c')
            ->join('l.content', 'lc')
            ->join('l.categories', 'c');
        if($categoryId)
            $qb->where('c.id='.$categoryId);
        $qb->orderBy('l.id');

        return new Paginator(new \Application\Paginator\DoctrineAdapter($qb->getQuery()));
    }
}
