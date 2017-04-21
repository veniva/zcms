<?php

namespace Logic\Tests\Core\Admin\Authenticate;


use Doctrine\ORM\EntityManager;
use Logic\Core\Admin\Authenticate\Register;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Logic\Core\Model\UserRepository;
use PHPUnit\Framework\TestCase;
use Logic\Core\Admin\Form\Register as RegisterForm;

class RegisterTest extends TestCase
{
    protected $emStub;
    protected $usrStub;
    protected $repoStb;
    protected $formStb;
    
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->emStub = $this->createMock(EntityManager::class);
        $this->usrStub = $this->createMock(User::class);
        $this->repoStb = $this->createMock(UserRepository::class);
        $this->emStub->method('getRepository')->willReturn($this->repoStb);
        $this->formStb = $this->createMock(RegisterForm::class);
    }

    public function testGetUsersExist()
    {
        $this->repoStb->method('countAdminUsers')->willReturn(1);
        $logic = new Register($this->emStub, $this->usrStub, $this->formStb);
        $result = $logic->getAction();

        $this->assertEquals(Register::ERR_USER_EXISTS, $result['status']);
    }

    public function testGetSuccess()
    {
        $this->repoStb->method('countAdminUsers')->willReturn(0);
        $logic = new Register($this->emStub, $this->usrStub, $this->formStb);
        $result = $logic->getAction();

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
    }

    public function testPostUserExist()
    {
        $this->repoStb->method('countAdminUsers')->willReturn(1);
        $logic = new Register($this->emStub, $this->usrStub, $this->formStb);
        $result = $logic->postAction([]);

        $this->assertEquals(Register::ERR_USER_EXISTS, $result['status']);
    }

    public function testInvalidForm()
    {
        $this->repoStb->method('countAdminUsers')->willReturn(0);
        $this->formStb->method('isValid')->willReturn(false);
        $logic = new Register($this->emStub, $this->usrStub, $this->formStb);
        $result = $logic->postAction([]);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result['status']);
    }
    
    public function testSuccess()
    {
        $this->repoStb->method('countAdminUsers')->willReturn(0);
        $this->formStb->method('isValid')->willReturn(true);

        $input = $this->createMock(Stb2::class);
        $input->method('getValue')->willReturn(true);
        $inputFilterStb = $this->createMock(Stb2::class);
        $inputFilterStb->method('getInputs')->willReturn([
            'isoCode' => $input,
            'language_name' => $input
        ]);
        
        $fieldset = $this->createMock(Stb2::class);
        $fieldset->method('get')->willReturn($input);

        $inputFilterStb->method('get')->willReturn($fieldset);

        $this->formStb->method('getInputFilter')->willReturn($inputFilterStb);

        $logic = new Register($this->emStub, $this->usrStub, $this->formStb);
        $result = $logic->postAction([]);

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);

    }
}

class Stb2
{
    function getInputs(){}
    function getValue(){}
    function get(){}
}