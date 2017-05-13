<?php

namespace Logic\Tests\Core\Adapters\Zend;

use Veniva\Lbs\Adapters\Zend\SendMail;
use PHPUnit\Framework\TestCase;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as Transport;

class SendMailTest extends TestCase
{
    public function testRunSendMethod()
    {
        $zendMessageStb = $this->getMockBuilder(Message::class)->setMethods(['send'])->getMock();
        $transportStb = $this->createMock(Transport::class);

        $sendMail = new \Veniva\Lbs\Adapters\Zend\SendMail();
        $sendMail->setZendMessage($zendMessageStb);
        $sendMail->setSendMail($transportStb);

        //send message with no headers
        $sendMail->send('example@example.com', 'example@example.com', 'Test', 'Test body');
        $this->assertTrue(true);

        //send message with headers
        $sendMail->send('example@example.com', 'example@example.com', 'Test', 'Test body', [
            'one' => true,
            'two' => true
        ]);
        $this->assertTrue(true);
    }

    public function testAddHeaders()
    {
        $headers = array(
            'header_one' => 'value_one',
            'header_two' => 'value_two'
        );

        $sendMail = new \Veniva\Lbs\Adapters\Zend\SendMail();
        $sendMail->setHeaders($headers);

        $mailHeaders = $sendMail->getHeaders();
        $this->assertTrue($mailHeaders instanceof Headers);
        $this->assertEquals('value_one', $mailHeaders->get('header_one')->getFieldValue());
        $this->assertEquals('value_two', $mailHeaders->get('header_two')->getFieldValue());
    }

    public function testDefaultHeader()
    {
        $sendMail = new \Veniva\Lbs\Adapters\Zend\SendMail();
        $sendMail->setHeaders([]);
        $mailHeaders = $sendMail->getHeaders();
        $this->assertTrue($mailHeaders instanceof Headers);
        $this->assertContains('charset="UTF-8"', $mailHeaders->get('Content-Type')->getFieldValue());
        $this->assertContains('text/plain', $mailHeaders->get('Content-Type')->getFieldValue());
    }
}