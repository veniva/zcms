<?php

namespace Application\Service\Factory;


use Logic\Core\Services\SendMail;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SendMailFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SendMail(new \Zend\Mail\Transport\Sendmail());
    }
}