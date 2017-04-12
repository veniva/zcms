<?php

namespace Tests\Model\Entity;

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

        $user->setRole(User::USER_USER);
        $result = $user->canEdit(User::USER_USER);

        $this->assertTrue($result);

        $user->setRole(User::USER_ADMIN);
        $result = $user->canEdit(User::USER_USER);

        $this->assertTrue($result);
    }
}