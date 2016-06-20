<?php

namespace ApplicationTest;


trait AuthorizationTrait
{
    /**
     * @param null|int $id The logged in user ID
     * @param null|int $role The logged in user role
     */
    public function mockLogin($id = 1, $role = 1)
    {
        if(!$id) $id = 1;
        if(!$role) $role = 1;

        $serviceLocator = $this->getApplicationServiceLocator();//use the app manager and not the test service manager

        $user = $serviceLocator->get('user-entity');
        $user->setId($id);
        $user->setUname('Tester');
        $user->setRole($role);

        $authService = $this->getMockBuilder('Zend\Authentication\AuthenticationService')->getMock();
        $authService->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($user));

        $authService->expects($this->any())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        $serviceLocator->setAllowOverride(true);
        $serviceLocator->setService('auth', $authService);
        
        return $user;
    }
}