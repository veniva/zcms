<?php

namespace Tests\Core\Admin\Authenticate;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ISendMail;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Authenticate\RestorePassword;
use Logic\Core\Admin\Form\RestorePasswordForm;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Logic\Core\Model\PasswordResetsRepository;
use PHPUnit\Framework\TestCase;

class RestorePasswordTest extends TestCase
{
    protected $formStb;
    protected $emStb;
    protected $trStb;

    /** @var RestorePassword */
    protected $restorePassword;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->formStb = $this->createMock(RestorePasswordForm::class);
        $this->emStb = $this->createMock(EntityManager::class);
        $this->trStb = $this->createMock(ITranslator::class);
        $this->restorePassword = new RestorePassword($this->formStb, $this->trStb);
    }

    protected function createSubs(&$inputStb, &$repoStb)
    {
        $inputStb = $this->createMock(StbPT::class);
        $inputStb->method('getValue')->willReturn(true);

        $repoStb = $this->createMock(StbPT::class);

        $this->emStb->method('getRepository')->willReturn($repoStb);
        $this->formStb->method('isValid')->willReturn(true);
        $this->formStb->method('get')->willReturn($inputStb);
    }

    public function testPostInvalidForm()
    {
        $this->formStb->method('isValid')->willReturn(false);
        $result = $this->restorePassword->postAction([], $this->emStb);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result['status']);
    }

    public function testNoEmailInDB()
    {
        $inputStb = $repoStb = null;
        $this->createSubs($inputStb, $repoStb);

        $repoStb->method('findOneByEmail')->willReturn(null);

        $result = $this->restorePassword->postAction([], $this->emStb);
        $this->assertEquals(RestorePassword::ERR_NOT_FOUND, $result['status']);
    }

    public function testNoEditAllowed()
    {
        $inputStb = $repoStb = null;
        $this->createSubs($inputStb, $repoStb);

        $userStb = $this->createMock(User::class);
        $userStb->method('getRole')->willReturn(3);

        $repoStb->method('findOneByEmail')->willReturn($userStb);

        $result = $this->restorePassword->postAction([], $this->emStb);
        $this->assertEquals(RestorePassword::ERR_NOT_ALLOWED, $result['status']);
    }

    public function testSuccess()
    {
        $inputStb = $repoStb = null;
        $this->createSubs($inputStb, $repoStb);

        $userStb = $this->createMock(User::class);
        $userStb->method('getRole')->willReturn(2);

        $repoStb->method('findOneByEmail')->willReturn($userStb);

        $result = $this->restorePassword->postAction([], $this->emStb);
        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
    }

    public function testSendEmailFailure()
    {
        $mailStb = $this->createMock(ISendMail::class);
        $mailStb->method('send')->willThrowException(new \Exception());

        $psStb = $this->createMock(PasswordResetsRepository::class);
        $psStb->method('deleteOldRequests')->willReturn(null);

        $this->emStb->method('getRepository')->willReturn($psStb);

        $result = $this->sendMail($mailStb);
        $this->assertEquals(RestorePassword::ERR_SEND_MAIL, $result['status']);
    }

    public function testSendMailSuccess()
    {
        $mailStb = $this->createMock(ISendMail::class);
        $mailStb->method('send')->willReturn(null);

        $psStb = $this->createMock(PasswordResetsRepository::class);
        $psStb->method('deleteOldRequests')->willReturn(null);

        $this->emStb->method('getRepository')->willReturn($psStb);

        $result = $this->sendMail($mailStb);
        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('email', $result);
    }

    protected function sendMail($mailStb)
    {
        return $this->restorePassword->persistAndSendEmail($this->emStb, $mailStb, [
            'email' => '',
            'no-reply' => '',
            'subject' => '',
            'message' => '',
            'token' => ''
        ]);
    }
}

class StbPT
{
    function findOneByEmail(){}
    function getValue(){}
}