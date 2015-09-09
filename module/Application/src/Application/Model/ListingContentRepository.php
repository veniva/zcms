<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 26/08/2015
 * Time: 14:50
 */

namespace Application\Model;


use Application\Model\Entity\ListingContent;
use Doctrine\ORM\EntityRepository;

class ListingContentRepository extends EntityRepository
{
    public function getListByAlias($alias)
    {
        $listingContentEntity = new ListingContent();
        $className = get_class($listingContentEntity);
        $dql = <<<TAG
SELECT lc FROM $className lc WHERE lc.alias = '$alias'
TAG;
        $listing = $this->getEntityManager()->createQuery($dql)->getResult();
        return $listing;
    }
}
