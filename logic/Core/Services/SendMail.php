<?php

namespace Logic\Core\Services;

use Logic\Core\Interfaces\ISendMail;
use Zend\Mail;

class SendMail implements ISendMail
{
    protected $transport;
    
    public function __construct(Mail\Transport\Sendmail $transport)
    {
        $this->transport = $transport;
    }
    
    public function send(string $fromEmail, string $toEmail, string $messageBody)
    {
        $message = new Mail\Message();
        $message->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setSubject('New password')
            ->setBody($messageBody);

        $transport = new Mail\Transport\Sendmail();
        $transport->send($message);
    }
    
    public function foo(){
        return 'foo';
    }
}