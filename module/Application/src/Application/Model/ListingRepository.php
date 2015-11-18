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

    public function getListingByAlias($alias, $lang)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
            ->join('l.content', 'lc')
            ->where('lc.alias=\''.$alias.'\'')
            ->andWhere('lc.lang='.$lang);
        $result = null;
        try{
            $result = $qb->getQuery()->getSingleResult();
        }catch(\Exception $ex){}
        return $result;
    }
}
