<?php

namespace Logic\Core\Admin\Authenticate;

use Zend\Authentication\AuthenticationService;

class Logout
{
    public static function logout(AuthenticationService $auth)
    {
        $auth->clearIdentity();
    }
}