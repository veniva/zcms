<?php

namespace Tests\Core\Adapters\Zend;

use Logic\Core\Adapters\Zend\SendMail;
use PHPUnit\Framework\TestCase;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as Transport;

class SendMailTest extends TestCase
{
    public function testRunSendMethod()
    {
        $zendMessageStb = $this->createMock(Message::class);
        $zendMessageStb->method('setFrom')->willReturnSelf();
        $zendMessageStb->method('setTo')->willReturnSelf();
        $zendMessageStb->method('setSubject')->willReturnSelf();

        $transportStb = $this->createMock(Transport::class);

        $sendMail = new SendMail();
        $sendMail->setZendMessage($zendMessageStb);
        $sendMail->setSendMail($transportStb);

        //send message with no headers
        $sendMail->send('', '', '', '');
        $this->assertTrue(true);

        //send message with headers
        $headersStb = $this->createMock(Headers::class);
        $zendMessageStb->method('getHeaders')->willReturn($headersStb);
        $sendMail->send('', '', '', '', [
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

        $sendMail = new SendMail();
        $sendMail->setHeaders($headers);

        $result = $sendMail->getHeaders();
        $this->assertTrue($result instanceof Headers);
        $this->assertEquals('value_one', $result->get('header_one')->getFieldValue());
        $this->assertEquals('value_two', $result->get('header_two')->getFieldValue());
    }
}