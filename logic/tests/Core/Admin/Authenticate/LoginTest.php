<?php

namespace Logic\Tests\Core\Admin\Authenticate;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Authenticate\Login;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\UserRepository;
use PHPUnit\Framework\TestCase;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Form\Form;

class LoginTest extends TestCase
{
    protected $emStub;
    protected $logic;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStub = $this->createMock(EntityManager::class);
        $translator = $this->createMock(ITranslator::class);
        $translator->method('translate')->willReturnArgument(0);

        $this->logic = new Login($translator);
    }

    protected function prepareGetStubs($users)
    {
        $repo = $this->createMock(UserRepository::class);
        $repo->method('countAdminUsers')->willReturn($users);
        $this->emStub->method('getRepository')->willReturn($repo);
    }

    public function testGetNoAdmin()
    {
        $this->prepareGetStubs(0);
        $result = $this->logic->inGet($this->emStub);
        $this->assertEquals(Login::ERR_NO_ADMIN, $result->status);
    }

    public function testGetSuccess()
    {
        $this->prepareGetStubs(1);
        $result = $this->logic->inGet($this->emStub);
        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }

    public function testGetFormMethod()
    {
        $form = $this->logic->getForm();
        $this->assertTrue($form instanceof Form);
    }

    public function testInvalidForm()
    {
        $formStub = $this->createMock(Form::class);
        $formStub->method('isValid')->willReturn(false);
        
        $this->logic->setForm($formStub);
        $result = $this->logic->validateFrom([], $id, $pass);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
    }

    public function testInvalidAuth()
    {
        $authStub = $formStub = $resultStub = null;
        $this->preparePostStubs($authStub, $formStub, $resultStub);
        
        $resultStub->method('isValid')->willReturn(false);
        $this->logic->setForm($formStub);
        $result = $this->logic->inPost($authStub, []);

        $this->assertEquals(Login::ERR_WRONG_DETAILS, $result->status);
    }

    public function testPostSuccess()
    {
        $authStub = $formStub = $resultStub = null;
        $this->preparePostStubs($authStub, $formStub, $resultStub);

        $resultStub->method('isValid')->willReturn(true);
        $resultStub->method('getIdentity')->willReturn(true);

        $this->logic->setForm($formStub);
        $result = $this->logic->inPost($authStub, []);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }

    protected function preparePostStubs(&$authStub, &$formStub, &$resultStub)
    {
        $formStub = $this->createMock(Form::class);
        $formStub->method('isValid')->willReturn(true);

        $mock = $this->getMockBuilder(\stdClass::class)->setMethods([
            'getValue', 'setIdentity', 'setCredential'
        ])->getMock();
        $mock->method('getValue')->willReturn('');
        $formStub->method('get')->willReturn($mock);

        $authStub = $this->createMock(AuthenticationService::class);
        $authStub->method('getAdapter')->willReturn($mock);

        $resultStub = $this->createMock(Result::class);
        $authStub->method('authenticate')->willReturn($resultStub);
    }
}