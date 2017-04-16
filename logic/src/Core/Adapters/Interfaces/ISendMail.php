<?php

namespace Logic\Core\Adapters\Interfaces;


interface ISendMail
{
    function send(string $fromEmail, string $toEmail, string $subject,  string $messageBody);
}