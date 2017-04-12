<?php

namespace Tests\Admin\User;

use Logic\Core\Admin\Form\User as UserForm;
use Logic\Core\Admin\User\UserUpdate;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Tests\Admin\AdminBase;

class UserUpdateTest extends AdminBase
{
    protected $logic;
    protected $usrStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->usrStb = $this->createMock(User::class);
        $this->logic = new UserUpdate($this->transStb, $this->emStb, $this->usrStb);
    }

    public function testShowInvalidParam()
    {
        $result = $this->logic->showForm(0);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testShowInvalidParam2()
    {
        $result = $this->logic->showForm(1);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testInsufficientPrivilegesError()
    {
        $this->emStb->method('find')->willReturn(new User());
        $this->usrStb->method('canEdit')->willreturn(false);

        $result = $this->logic->showForm(1);

        $this->assertEquals(UserUpdate::ERR_INSUFFICIENT_PRIVILEGES, $result->status);
        $this->assertTrue(strlen($result->message) > 0);
    }

    public function testShowSuccess()
    {
        $this->emStb->method('find')->willReturn(new User());
        $this->usrStb->method('canEdit')->willreturn(true);

        $result = $this->logic->showForm(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->get('form') instanceof UserForm);
        $this->assertTrue($result->get('user') instanceof User);
        $this->assertTrue($result->has('edit_own'));
    }
}