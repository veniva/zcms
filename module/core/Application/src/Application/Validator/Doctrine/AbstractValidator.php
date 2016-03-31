<?php

namespace Application\Validator\Doctrine;


use Doctrine\ORM\EntityManager;
use Zend\Validator\AbstractValidator as ZendAbstractValidator;
use Zend\Validator\Exception;

abstract class AbstractValidator extends ZendAbstractValidator
{
    /**
     * @var EntityManager
     */
    private $em;


    public function __construct(EntityManager $em, $options = null)
    {
        parent::__construct($options);
        $this->em = $em;
    }

    public function getEm(){
        return $this->em;
    }
}