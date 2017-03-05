<?php

namespace Logic\Core;


use Logic\Core\Adapters\Interfaces\ITranslator;

class Response
{
    /** @var ITranslator */
    protected $translator;
    
    public function __construct(ITranslator $translator = null)
    {
        if($translator)
            $this->translator = $translator;
    }

    public function response($status, string $message = null, array $array = []):array
    {
        $response = [
            'status' => $status
        ];
        
        if($message){
            if($this->translator)
                $response['message'] = $this->translator->translate($message);
            else
                $response['message'] = $message;
        }
        
        $response = array_merge($response, $array);

        return $response;
    }

    /**
     * @return ITranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param ITranslator $translator
     * @return Response
     */
    public function setTranslator(ITranslator $translator)
    {
        $this->translator = $translator;
        return $this;
    }
    
}