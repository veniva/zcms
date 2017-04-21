<?php

namespace Logic\Tests\Core\Model\Entity;

use Logic\Core\Model\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCanEdit()
    {
        $user = new User();
        $user->setRole(User::USER_GUEST);
        $result = $user->canEdit(User::USER_ADMIN);

        $this->assertFalse($result);

        //test with role = NULL
        $user->setRole(null);
        $result = $user->canEdit(User::USER_GUEST);

        $this->assertFalse($result);

        //test with edited role = NULL (new user)
        $user->setRole(User::USER_GUEST);
        $result = $user->canEdit(null);

        $this->assertTrue($result);

        $user->setRole(User::USER_USER);
        $result = $user->canEdit(User::USER_USER);

        $this->assertTrue($result);

        $user->setRole(User::USER_ADMIN);
        $result = $user->canEdit(User::USER_USER);

        $this->assertTrue($result);
    }
}