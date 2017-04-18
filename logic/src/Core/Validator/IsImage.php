<?php

namespace Logic\Core\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class IsImage extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const CORRUPTED_FILE = 'fileCorrupted';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::CORRUPTED_FILE => "File is corrupted",
    ];

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
        if(empty($value) || !@imagecreatefromstring($value)){
            $this->error(self::CORRUPTED_FILE);
            return false;
        }
        return true;
    }
}