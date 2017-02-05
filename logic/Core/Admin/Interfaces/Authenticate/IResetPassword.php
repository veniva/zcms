<?php

namespace Logic\Core\Admin\Interfaces\Authenticate;


use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Adapters\Interfaces\Http\IRequest;
use Zend\Form\Form;

interface IResetPassword
{
    function resetGet(IRequest $request, EntityManagerInterface $em): array;
    function resetPost(IRequest $request, EntityManagerInterface $em): array;
    function form(): Form;
}