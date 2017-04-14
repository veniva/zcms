<?php

namespace Tests\Admin\User;

use Logic\Core\Admin\Form\User as UserForm;
use Logic\Core\Admin\User\UserCreate;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Tests\Admin\AdminBase;

class UserCreateTest extends AdminBase
{
    protected $logic;
    protected $userStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->userStb = $this->createMock(User::class);
        $this->logic = new UserCreate($this->transStb, $this->emStb, $this->userStb);
    }

    public function testShowForm()
    {
        $result = $this->logic->showForm();

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->get('user') instanceof User);
        $this->assertTrue($result->get('form') instanceof UserForm);
    }
}