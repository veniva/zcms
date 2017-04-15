<?php

namespace Tests\Admin\User;

use Logic\Core\Admin\Form\User as UserForm;
use Logic\Core\Admin\User\UserCreate;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Tests\Admin\AdminBase;
use Zend\Crypt\Password\Bcrypt;

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

    public function testCreateInvalidForm()
    {
        $result = $this->logic->create([], new Bcrypt());

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_FORM_MSG, $result->message);
    }

    public function testCreateCannotAssignRole()
    {
        $formStb = $this->createMock(UserForm::class);
        $formStb->method('getData')->willReturn(new usrCrStb());

        $result = $this->logic->createUser(new User(), $formStb);

        $this->assertEquals(UserCreate::ERR_NO_RIGHT_ASSIGN_ROLE, $result->status);
        $this->assertEquals(UserCreate::ERR_NO_RIGHT_ASSIGN_ROLE_MSG, $result->message);
    }

    public function testCreateUserSuccess()
    {
        $stb = $this->createMock(usrCrStb::class);
        $stb->method('get')->willReturn($stb);
        $this->userStb->method('canEdit')->willReturn(true);

        $formStb = $this->createMock(UserForm::class);
        $formStb->method('getData')->willReturn($stb);
        $formStb->method('getInputFilter')->willReturn($stb);

        $result = $this->logic->createUser($this->userStb, $formStb);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(strlen($result->message) > 0);
    }
}

class usrCrStb
{
    function getRole(){}
    function getInputFilter(){}
    function get(){}
    function getValue(){}
    function getPasswordAdapter(){}
}
