<?php

namespace Application\Authentication;


use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result;
use Zend\ServiceManager;

class Adapter extends AbstractAdapter implements ServiceManager\ServiceLocatorAwareInterface
{
    use ServiceManager\ServiceLocatorAwareTrait;

    /**
     * Authenticate against database credentials
     */
    public function authenticate()
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $userEntity = $this->getServiceLocator()->get('user-entity');
        $user = $entityManager->getRepository(get_class($userEntity))->findOneByUname($this->getIdentity());
        if($user && $user->getRole() <= $user::USER_ADMIN && $user->checkPassword($this->getCredential())){
            return new Result(Result::SUCCESS, $user);
        }

        return new Result(Result::FAILURE, $this->getIdentity());
    }
}
