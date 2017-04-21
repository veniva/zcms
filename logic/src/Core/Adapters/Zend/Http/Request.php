<?php

namespace Logic\Core\Adapters\Zend\Http;


use Logic\Core\Adapters\Interfaces\Http\IRequest;
use Zend\Http\Request as ZendRequest;

class Request implements IRequest
{
    /** @var  ZendRequest */
    protected $request;

    /**
     * @deprecated
     * 
     * Request constructor.
     * @param ZendRequest $request
     */
    public function __construct(ZendRequest $request)
    {
        $this->request = $request;
    }

    public function isPost(): bool
    {
        return $this->request->isPost();
    }

    public function getPost(string $name = null, $default = null): array
    {
        return iterator_to_array($this->request->getPost($name, $default));
    }

    /**
     * @param string|null $name
     * @param null $default
     * @return array|string
     */
    public function getQuery(string $name = null, $default = null)
    {
        if($name === null){
            return iterator_to_array($this->request->getQuery());
        }else{
            return (string) $this->request->getQuery($name, $default);
        }
        
        
    }
}