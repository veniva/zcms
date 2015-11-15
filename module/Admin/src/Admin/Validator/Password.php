<?php
namespace Admin\Validator;


use Application\Model\Entity\User;
use Zend\Validator\StringLength;
use Zend\Validator\Exception;

class Password extends StringLength
{
    const MISSING_LOWER_CASE    = 'stringMissingLowerCase';
    const MISSING_UPPER_CASE    = 'stringMissingUpperCase';
    const MISSING_NUMBER        = 'stringMissingNumber';

    public function __construct()
    {
        $this->messageTemplates[self::MISSING_LOWER_CASE] = "There must be at least one lower case letter";
        $this->messageTemplates[self::MISSING_UPPER_CASE] = "There must be at least one upper case letter";
        $this->messageTemplates[self::MISSING_NUMBER] = "There must be at least one number";

        parent::__construct(['min' => User::PASS_MIN_LENGTH, 'max' => User::PASS_MAX_LENGTH]);
    }

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
        if(is_string($value)){
            //has one lower case
            if(!preg_match('/[a-z]+/', $value) && !preg_match('/[абвгдежзийклмнопрстуфхцчшщъюя]+/', $value)){
                $this->error(self::MISSING_LOWER_CASE);
            }
            //has one upper case
            if(!preg_match('/[A-Z]+/', $value) && !preg_match('/[АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЮЯ]+/', $value)){
                $this->error(self::MISSING_UPPER_CASE);
            }
            //has number
            if(!preg_match('/[1-9]+/', $value)){
                $this->error(self::MISSING_NUMBER);
            }

            if (count($this->getMessages())) {
                return false;
            }
        }
        $this->some = 6;
        return parent::isValid($value);
    }
}