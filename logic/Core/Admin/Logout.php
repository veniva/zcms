<?php

namespace Logic\Core\Admin;


use Zend\Authentication\AuthenticationService;

class Logout
{
    public static function logout(AuthenticationService $auth)
    {
        $auth->clearIdentity();
    }
}