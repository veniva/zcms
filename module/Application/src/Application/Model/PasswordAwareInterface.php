<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
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