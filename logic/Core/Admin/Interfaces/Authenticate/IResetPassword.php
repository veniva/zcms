<?php

namespace Logic\Core\Admin\Interfaces\Authenticate;


use Zend\Form\Form;

interface IResetPassword
{
    function resetGet(): array;
    function resetPost(): array;
    function form(): Form;
}