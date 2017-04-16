<?php

namespace Logic\Core\Admin\Interfaces\Authenticate;


use Doctrine\ORM\EntityManagerInterface;
use Zend\Form;
use Logic\Core\Adapters\Interfaces\ISendMail;

interface IRestorePassword
{
    function getAction():Form\Form;
    function postAction(array $data, EntityManagerInterface $em):array;
    function persistAndSendEmail(EntityManagerInterface $em, ISendMail $sendMail, array $data);
    function form():Form\Form;
}