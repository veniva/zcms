<?php

namespace Logic\Core\Adapters\Interfaces;


interface ITranslator
{
    function translate(string $value): string;
}