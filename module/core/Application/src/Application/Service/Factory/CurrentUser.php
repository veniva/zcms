<?php

namespace Application\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class CurrentUser implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $auth = $container->get('auth');

        if($auth->hasIdentity()){
            $user = $auth->getIdentity();
            if(empty($user->getRole())) $user->setRoleFromName($config['acl']['defaults']['role']['admin']);
        }else{
            $user = $container->get('user-entity');
            $user->setId(null);
            $user->setRoleFromName($config['acl']['defaults']['role']['guest']);
        }
        return $user;
    }
}