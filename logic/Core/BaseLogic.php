<?php

namespace Logic\Core;


use Logic\Core\Adapters\Interfaces\ITranslator;

class BaseLogic
{
    /** @var Result */
    protected $result;
    
    public function __construct(ITranslator $translator = null)
    {
        $this->result = new Result($translator);
    }
    
    protected function result($status, string $message = null, array $array = []):Result
    {
        return $this->result->set($status, $message, $array);
    }
}