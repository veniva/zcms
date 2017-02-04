<?php

namespace Logic\Core\Interfaces;


interface ISendMail
{
    function send(string $fromEmail, string $toEmail, string $messageBody);
}