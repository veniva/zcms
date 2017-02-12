<?php

namespace Logic\Core\Adapters\Zend;


use Logic\Core\Adapters\Interfaces\ISendMail;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as Transport;

class SendMail implements ISendMail
{

    public function send(string $fromEmail, string $toEmail, string $subject, string $messageBody)
    {
        $message = new Message();
        $sendMail = new Transport;

        $message->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setSubject($subject)
            ->setBody($messageBody);

        $sendMail->send($message);
    }
}