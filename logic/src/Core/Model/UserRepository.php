<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Model;

use Veniva\Lbs\Adapters\DoctrineAdapter;
use Logic\Core\Model\Entity\User;
use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator;

class UserRepository extends EntityRepository
{
    /**
     * @param int $userRoleId The highest user role the user has a right to edit
     * @return Paginator
     */
    public function getEditableUsersPaginated(int $userRoleId)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
            ->where('u.role >='.$userRoleId);
        
        return new Paginator(new DoctrineAdapter($qb->getQuery()));
    }

    public function countAdminUsers()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select($qb->expr()->count('u'))
            ->where('u.role <='.User::USER_ADMIN);
        return $qb->getQuery()->getSingleScalarResult();
    }
}