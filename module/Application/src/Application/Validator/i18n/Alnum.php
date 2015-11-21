<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Validator\i18n;


use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class Alnum extends AbstractValidator
{
    const NOT_ALNUM    = 'notAlnum';

    protected $messageTemplates = array(
        self::NOT_ALNUM    => "The input may contain only latin characters and numbers",
    );

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if(!($return = preg_match('/^[0-9a-zA-Z]+$/', $value))){
            $this->error(self::NOT_ALNUM);
        }
        return $return;
    }
}