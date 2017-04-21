<?php
namespace Logic\Tests\Core\Admin\User;

use Logic\Core\Admin\Form\User as UserForm;
use Logic\Core\Admin\User\UserUpdate;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Logic\Tests\Core\Admin\AdminBase;

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

    public function testUpdateReturnError()
    {
        $result = $this->logic->update(0, []);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testInvalidForm()
    {
        $this->emStb->method('find')->willReturn(new User());
        $this->usrStb->method('canEdit')->willreturn(true);

        $result = $this->logic->update(1, []);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertTrue($result->get('form') instanceof UserForm);
        $this->assertTrue($result->get('user') instanceof User);
        $this->assertTrue($result->has('edit_own'));
    }

    public function testCannotAssignRoleError()
    {
        $stb = $this->createMock(usrUpdStb::class);
        $this->usrStb->method('canEdit')->willreturn(false);

        $formStb = $this->createMock(UserForm::class);
        $formStb->method('isValid')->willReturn(true);
        $formStb->method('getData')->willReturn($stb);

        $result = $this->logic->updateUser(new User, $formStb, [], false);

        $this->assertEquals(UserUpdate::ERR_NO_RIGHT_ASSIGN_ROLE, $result->status);
        $this->assertEquals(UserUpdate::ERR_NO_RIGHT_ASSIGN_ROLE_MSG, $result->message);
    }

    public function testCannotAssignRoleYourself()
    {
        $stb = $this->createMock(usrUpdStb::class);
        $this->usrStb->method('canEdit')->willreturn(true);

        $formStb = $this->createMock(UserForm::class);
        $formStb->method('isValid')->willReturn(true);
        $formStb->method('getData')->willReturn($stb);

        $result = $this->logic->updateUser(new User, $formStb, ['role' => 1], true);

        $this->assertEquals(UserUpdate::ERR_SELF_NEW_ROLE, $result->status);
        $this->assertTrue(strlen($result->message) > 0);
    }

    public function testUpdateSuccess()
    {
        $stb = $this->createMock(usrUpdStb::class);
        $stb->method('get')->willReturn($stb);
        $this->usrStb->method('canEdit')->willreturn(true);

        $formStb = $this->createMock(UserForm::class);
        $formStb->method('isValid')->willReturn(true);
        $formStb->method('getData')->willReturn($stb);
        $formStb->method('getInputFilter')->willReturn($stb);

        $result = $this->logic->updateUser(new User, $formStb, ['role' => 1], false);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(strlen($result->message) > 0);
    }
}

class usrUpdStb
{
    function getRole(){}
    function getInputFilter(){}
    function get(){}
    function getValue(){}
}
