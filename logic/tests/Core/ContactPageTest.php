<?php

namespace Tests\Core;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ISendMail;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Adapters\Zend\SendMail;
use Logic\Core\ContactPage;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Logic\Core\Model\UserRepository;
use PHPUnit\Framework\TestCase;
use Zend\Form\Element;
use Logic\Core\Form\Contact as ContactForm;

class ContactPageTest extends TestCase
{
    protected $emStub;
    protected $formStub;
    protected $repoStub;
    protected $userStub;
    protected $mailStub;
    protected $trStub;

    public function __construct()
    {
        $this->emStub = $this->createMock(EntityManager::class);
        $this->repoStub = $this->createMock(UserRepository::class);
        $this->formStub = $this->createMock(ContactForm::class);
        $this->userStub = $this->createMock(User::class);
        $this->mailStub = $this->createMock(ISendMail::class);
        $this->trStub = $this->createMock(ITranslator::class);
    }

    protected function prepareMethods()
    {
        $this->emStub->method('getRepository')->willReturn($this->repoStub);
        $this->repoStub->method('__call')->with('findOneByRole')->willReturn($this->userStub);
        $this->trStub->method('translate')->will($this->returnArgument(0));
    }

    public function testShow()
    {
        $this->prepareMethods();

        $logic = new ContactPage($this->emStub, $this->formStub, $this->mailStub);
        $result = $logic->showPage();

        $this->assertEquals(ContactPage::SHOW_FORM, $result['status']);
    }

    public function testProcessInvalidForm()
    {
        $this->prepareMethods();
        $this->formStub->method('isValid')->willReturn(false);

        $logic = new ContactPage($this->emStub, $this->formStub, $this->mailStub);
        $result = $logic->processForm($this->trStub, []);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result['status']);
    }

    public function testSuccess()
    {
        $inputStub = $this->createMock(Element::class);
        $inputStub->method('getValue')->willReturn('str');
        $this->formStub->method('get')->willReturn($inputStub);
        $this->prepareMethods();
        $this->formStub->method('isValid')->willReturn(true);
        $this->userStub->method('getEmail')->willReturn('str');

        $logic = new ContactPage($this->emStub, $this->formStub, $this->mailStub);
        $result = $logic->processForm($this->trStub, []);

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
    }
    
    public function testForm()
    {
        $form = new ContactForm('', '');
        $inputFilter = $form->getInputFilter();

        //region Test Email
        $emailInput = $inputFilter->get('email');

        //test invalid email
        $emailInput->setValue('asa@');
        $this->assertFalse($emailInput->isValid());

        //test valid email
        $emailInput->setValue('example@mail.com');
        $this->assertTrue($emailInput->isValid());

        //test missing email
        $emailInput->setValue('');
        $this->assertFalse($emailInput->isValid());
        //endregion

        //region Test Name
        $nameInput = $inputFilter->get('name');

        //test empty value
        $nameInput->setValue('');
        $this->assertFalse($nameInput->isValid());

        //test success
        $nameInput->setValue('asa');
        $this->assertTrue($nameInput->isValid());
        //endregion

        //region Test inquiry
        $inquiryInput = $inputFilter->get('inquiry');

        $inquiryInput->setValue('');
        $this->assertFalse($inquiryInput->isValid());

        $inquiryInput->setValue('ada');
        $this->assertTrue($inquiryInput->isValid());
        //endregion
    }

    public function testFormatBody()
    {
        $this->prepareMethods();
        $logic = new ContactPage($this->emStub, $this->formStub, $this->mailStub);
        $name = 'Dudley'; $text = 'How are you';
        $body = $logic->formatBody($this->trStub, $name, $text);

        $this->assertTrue(is_string($body));

        $expected = 'From '.$name.":\n\n".$text;
        $this->assertEquals($expected, $body);
    }

    public function testSendEmailCalled()
    {
        //configure stubs
        $this->prepareMethods();
        $this->formStub->method('isValid')->willReturn(true);
        $this->userStub->method('getEmail')->willReturn('str@str.str');

        $inputStub = $this->createMock(Element::class);
        $inputStub->method('getValue')->willReturn('str');
        $this->formStub->method('get')->willReturn($inputStub);


        $sendMail = $this->getMockBuilder(SendMail::class)->setMethods(['send'])->getMock();
        $sendMail->expects($this->once())->method('send');
        $contactPage = new ContactPage($this->emStub, $this->formStub, $sendMail);
        $contactPage->processForm($this->trStub, []);
    }
}