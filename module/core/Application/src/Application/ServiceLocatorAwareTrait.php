<?php

namespace Application;

use Interop\Container\ContainerInterface;

trait ServiceLocatorAwareTrait
{
    /**
     * @var ContainerInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ContainerInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ContainerInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}