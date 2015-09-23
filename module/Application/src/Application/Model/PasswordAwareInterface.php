<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 15/09/2015
 * Time: 16:26
 */

namespace Application\Model;


use Zend\Crypt\Password\PasswordInterface;

interface PasswordAwareInterface
{
    /**
     * @param PasswordInterface $adapter
     */
    public function setPasswordAdapter(PasswordInterface $adapter);

    /**
     * @return PasswordInterface
     */
    public function getPasswordAdapter();
}