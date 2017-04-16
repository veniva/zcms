<?php

namespace Logic\Core;


use Logic\Core\Adapters\Interfaces\ITranslator;

class BaseLogic
{
    /** @var Result */
    protected $result;
    
    /** @var null|ITranslator */
    protected $translator;
    
    public function __construct(ITranslator $translator = null)
    {
        $this->translator = $translator;
        
        $this->result = new Result($translator);
    }
    
    protected function result($status, string $message = null, array $array = []):Result
    {
        return $this->result->set($status, $message, $array);
    }
}