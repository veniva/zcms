<?php

namespace Application\Service\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CurrentUser implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $auth = $serviceLocator->get('auth');

        if($auth->hasIdentity()){
            $user = $auth->getIdentity();
            if(empty($user->getRole())) $user->setRoleFromName($config['acl']['defaults']['role']['admin']);
        }else{
            $user = $serviceLocator->get('user-entity');
            $user->setId(null);
            $user->setRoleFromName($config['acl']['defaults']['role']['guest']);
        }
        return $user;
    }
}