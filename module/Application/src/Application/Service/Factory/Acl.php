<?php

namespace Application\Service\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Permissions\Acl\Acl as AccessControlList;

class Acl implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $aclConfig = $config['acl'];
        $accessControlList = new AccessControlList();

        //add the resources
        foreach($aclConfig['resource'] as $resource => $parent){
            $accessControlList->addResource($resource, $parent);
        }

        //add the roles
        foreach($aclConfig['role'] as $role => $parents){
            $accessControlList->addRole($role, $parents);
        }

        //allow, deny
        foreach(array('allow', 'deny') as $action){
            foreach($aclConfig[$action] as $definition){
                call_user_func_array(array($accessControlList, $action), $definition);
            }
        }
        return $accessControlList;
    }
}