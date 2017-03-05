<?php

namespace Logic\Core;


use Logic\Core\Adapters\Interfaces\ITranslator;

class BaseLogic
{
    /** @var Response */
    protected $response;
    
    public function __construct(ITranslator $translator = null)
    {
        $this->response = new Response($translator);
    }
    
    protected function response($status, string $message = null, array $array = []):array
    {
        return $this->response->response($status, $message, $array);
    }
}