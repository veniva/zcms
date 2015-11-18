<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Model;

use Doctrine\ORM\EntityRepository;
use Zend\Authentication\AuthenticationService;
use Zend\Paginator\Paginator;

class UserRepository extends EntityRepository
{
    public function getUsersPaginated()
    {
        $auth = new AuthenticationService();
        $qb = $this->createQueryBuilder('u');
        $qb->select('u');
        if($auth->hasIdentity()){
            $qb->where('u.role >='.$auth->getIdentity()->getRole());
        }
        return new Paginator(new \Application\Paginator\DoctrineAdapter($qb->getQuery()));
    }
}