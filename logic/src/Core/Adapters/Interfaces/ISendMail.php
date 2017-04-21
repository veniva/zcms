<?php

namespace Logic\Core\Adapters\Interfaces;

interface ISendMail
{
    function send(string $fromEmail, string $toEmail, string $subject,  string $messageBody, array $headers = []);
    function setHeaders(array $headers);
    function getHeaders();
}