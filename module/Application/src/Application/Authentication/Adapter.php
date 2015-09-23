<?php

namespace Application\Authentication;


use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Adapter extends AbstractAdapter implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Authenticate against database credentials
     */
    public function authenticate()
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $userEntity = $this->getServiceLocator()->get('user-entity');
        $user = $entityManager->getRepository(get_class($userEntity))->findOneByEmail($this->getIdentity());
        if($user && $user->checkPassword($this->getCredential())){
            return new Result(Result::SUCCESS, $user);
        }

        return new Result(Result::FAILURE, $this->getIdentity());
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
