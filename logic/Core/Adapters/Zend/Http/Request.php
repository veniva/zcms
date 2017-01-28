<?php

namespace Logic\Core\Adapters\Zend\Http;


use Logic\Core\Adapters\Interfaces\Http\IRequest;
use Zend\Http\Request as ZendRequest;

class Request implements IRequest
{
    /** @var  ZendRequest */
    protected $request;
    
    public function __construct(ZendRequest $request)
    {
        $this->request = $request;
    }

    function isPost(): bool
    {
        return $this->request->isPost();
    }

    function getPost(string $name = null, $default = null): array
    {
        return iterator_to_array($this->request->getPost($name, $default));
    }
}