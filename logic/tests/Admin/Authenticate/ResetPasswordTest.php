<?php

namespace Tests\Admin\Authenticate;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Authenticate\ResetPassword;
use Logic\Core\Admin\Form\ResetPassword as ResetPasswordForm;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\PasswordResets;
use Logic\Core\Model\Entity\User;
use PHPUnit\Framework\TestCase;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterInterface;

class ResetPasswordTest extends TestCase
{
    protected $emStb;
    protected $formStb;
    protected $transStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->formStb = $this->createMock(ResetPasswordForm::class);
        $this->transStb = $this->createMock(ITranslator::class);
    }
    
    public function testGetBrokenLink()
    {
        $reset = new ResetPassword($this->emStb, $this->formStb, $this->transStb);
        $result = $reset->resetGet();

        $this->assertEquals(ResetPassword::ERR_BROKEN_LINK, $result['status']);
    }

    public function testGetNotFound()
    {
        $this->emStb->method('find')->willReturn(false);
        $reset = new ResetPassword($this->emStb, $this->formStb, $this->transStb, 's', 's');
        $result = $reset->resetGet();

        $this->assertEquals(ResetPassword::ERR_PASSWORD_REQUEST_NOT_FOUND, $result['status']);
    }

    public function testGetTooOld()
    {
        $prStb = $this->createMock(PasswordResets::class);
        $prStb->method('getCreatedAt')->willReturn(new \DateTime("1 year ago"));
        $this->emStb->method('find')->willReturn($prStb);

        $reset = new ResetPassword($this->emStb, $this->formStb, $this->transStb, 's', 's');
        $result = $reset->resetGet();

        $this->assertEquals(ResetPassword::ERR_LINK_TOO_OLD, $result['status']);
    }

    public function testGetUnExistingUser()
    {
        $prStb = $this->createMock(PasswordResets::class);
        $prStb->method('getCreatedAt')->willReturn(new \DateTime());
        $this->emStb->method('find')->willReturn($prStb);

        $repoStb = $this->repoStb();
        $repoStb->method('findOneByEmail')->willReturn(false);

        $reset = new ResetPassword($this->emStb, $this->formStb, $this->transStb, 's', 's');
        $result = $reset->resetGet();

        $this->assertEquals(ResetPassword::ERR_UNEXISTING_USER, $result['status']);
    }

    public function testGetSuccess()
    {
        $this->successStubs();

        $reset = new ResetPassword($this->emStb, $this->formStb, $this->transStb, 's', 's');
        $result = $reset->resetGet();

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
    }

    protected function repoStb()
    {
        $repoStb = $this->createMock(StbRst::class);
        $this->emStb->method('getRepository')->willReturn($repoStb);

        return $repoStb;
    }

    protected function passwordResetStb()
    {
        $prStb = $this->createMock(PasswordResets::class);
        $prStb->method('getCreatedAt')->willReturn(new \DateTime());
        $this->emStb->method('find')->willReturn($prStb);
    }

    protected function successStubs()
    {
        $this->passwordResetStb();
        $repoStb = $this->repoStb();
        $repoStb->method('findOneByEmail')->willReturn(true);
    }
    
    public function testPostInvalidForm()
    {
        $this->successStubs();
        $this->formStb->method('isValid')->willReturn(false);
        $reset = new ResetPassword($this->emStb, $this->formStb, $this->transStb, 's', 's');

        $result = $reset->resetPost([]);
        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result['status']);
    }

    public function testPostSuccess()
    {
        $this->passwordResetStb();
        $repoStb = $this->repoStb();

        $usrStb = $this->createMock(User::class);
        $repoStb->method('findOneByEmail')->willReturn($usrStb);

        $this->formStb->method('isValid')->willReturn(true);

        $inputFilterStb = $this->createMock(InputFilterInterface::class);
        $this->formStb->method('getInputFilter')->willReturn($inputFilterStb);

        $inputFieldStb = $this->createMock(Fieldset::class);
        $inputFilterStb->method('get')->willReturn($inputFieldStb);

        $inputStb = $this->createMock(Element::class);
        $inputFieldStb->method('get')->willReturn($inputStb);

        $reset = new ResetPassword($this->emStb, $this->formStb, $this->transStb, 's', 's');

        $result = $reset->resetPost([]);
        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
    }
}

class StbRst{
    function findOneByEmail(){}
    function deleteAllForEmail(){}
}