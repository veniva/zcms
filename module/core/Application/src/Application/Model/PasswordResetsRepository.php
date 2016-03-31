<?php

namespace Application\Model;


use Doctrine\ORM\EntityRepository;

class PasswordResetsRepository extends EntityRepository
{
    public function deleteOldRequests()
    {
        $qb = $this->createQueryBuilder('pr');
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT24H'));
        $qb->delete()
            ->where($qb->expr()->lt('pr.createdAt', '?1'))
            ->setParameter(1, $date->format('Y-m-d H:i:s'));
        return $qb->getQuery()->execute();
    }

    public function deleteAllForEmail($email)
    {
        $qb = $this->createQueryBuilder('pr');
        $qb->delete()->where($qb->expr()->eq('pr.email', '?1'));
        return $qb->getQuery()->execute([1 => $email]);
    }
}