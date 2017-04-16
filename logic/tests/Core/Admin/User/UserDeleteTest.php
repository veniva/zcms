<?php

namespace Tests\Core\Admin\User;

use Logic\Core\Admin\User\UserDelete;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Tests\Core\Admin\AdminBase;

class UserDeleteTest extends AdminBase
{
    protected $usrStb;
    protected $logic;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->usrStb = $this->createMock(User::class);
        $this->logic = new UserDelete($this->transStb, $this->emStb, $this->usrStb);
    }

    public function testDeleteInvalidParam()
    {
        $result = $this->logic->delete(0);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }


    public function testDeleteInvalidParam2()
    {
        $result = $this->logic->delete(1);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testCannotDeleteOwnProfile()
    {
        $usrStb = $this->createMock(User::class);
        $this->emStb->method('find')->willReturn($usrStb);

        $result = $this->logic->delete(1);

        $this->assertEquals(UserDelete::ERR_CAN_NOT_DELETE_OWN_PROFILE, $result->status);
        $this->assertTrue(strlen($result->message) > 0);
    }

    public function testNoPrivileges()
    {
        $usrStb = $this->createMock(User::class);
        $usrStb->method('getId')->willReturn(1);
        $this->emStb->method('find')->willReturn($usrStb);
        $this->usrStb->method('canEdit')->willReturn(false);

        $result = $this->logic->delete(1);

        $this->assertEquals(UserDelete::ERR_INSUFFICIENT_RIGHTS, $result->status);
        $this->assertTrue(strlen($result->message) > 0);
    }

    public function testDeleteSuccess()
    {
        $usrStb = $this->createMock(User::class);
        $usrStb->method('getId')->willReturn(1);
        $this->emStb->method('find')->willReturn($usrStb);
        $this->usrStb->method('canEdit')->willReturn(true);

        $result = $this->logic->delete(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(strlen($result->message) > 0);
    }
}