<?php

namespace Logic\Core\Adapters\Zend;

use Logic\Core\Adapters\Interfaces\ISendMail;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as Transport;
use Zend\Mail\Header\GenericHeader;

class SendMail implements ISendMail
{
    /** @var Message */
    protected $zendMessage;

    /** @var Transport */
    protected $sendMail;

    public function __construct()
    {
        $this->zendMessage = new Message();
        $this->sendMail = new Transport;
    }

    public function send(string $fromEmail, string $toEmail, string $subject, string $messageBody, array $headers = [])
    {
        //set new headers
        $this->setHeaders($headers);

        $message = $this->zendMessage;
        $sendMail = $this->sendMail;

        $message->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setSubject($subject)
            ->setBody($messageBody);

        $sendMail->send($message);
    }

    public function setHeaders(array $headers)
    {
        $messageHeaders = $this->zendMessage->getHeaders();
        if ($headers) {
            foreach ($headers as $header => $value) {
                $newHeader = new GenericHeader($header, $value);
                $messageHeaders->addHeader($newHeader);
            }
            
        //add a default header
        } else {
            $newHeader = new GenericHeader('Content-Type', 'text/plain; charset=UTF-8');
            $messageHeaders->addHeader($newHeader);
        }
        $this->zendMessage->setHeaders($messageHeaders);
    }

    public function getHeaders()
    {
        return $this->zendMessage->getHeaders();
    }

    /**
     * @return Message
     */
    public function getZendMessage()
    {
        return $this->zendMessage;
    }

    /**
     * @param Message $zendMessage
     * @return SendMail
     */
    public function setZendMessage($zendMessage)
    {
        $this->zendMessage = $zendMessage;
        return $this;
    }

    /**
     * @return Transport
     */
    public function getSendMail()
    {
        return $this->sendMail;
    }

    /**
     * @param Transport $sendMail
     * @return SendMail
     */
    public function setSendMail($sendMail)
    {
        $this->sendMail = $sendMail;
        return $this;
    }
}