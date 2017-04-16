<?php

namespace Logic\Core\Adapters\Interfaces\Http;


interface IRequest
{
    function isPost(): bool;
    function getPost(string $name = null, $default = null): array;
    function getQuery(string $name = null, $default = null);
}