<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Validator;


use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class Base64String extends AbstractValidator
{

    const INVALID   = 'stringLengthInvalid';
    const TOO_LONG  = 'stringLengthTooLong';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid data.",
        self::TOO_LONG  => "The file is more than %max%%measure% long",
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'max' => ['options' => 'max'],
        'measure' => ['options' => 'measure'],
    ];

    protected $options = [
        'max'      => null,    // Maximum length in Kb, null if there is no length limitation
        'measure' => 'K', // K by default. Possible values - K, MB
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value The base64 encoded string (with no metadata prepended)
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }
        $bytes = ((strlen($value) * 3) / 4);//base 64 encoded is about 33% bigger then the original
        switch($this->options['measure']){
            case 'MB':
                $divisor = 1048576;
                break;
            default:
                $divisor = 1024;
        }
        $size = $bytes / $divisor;//size in K or in MB
        if($size > $this->options['max']){
            $this->error(self::TOO_LONG);
            return false;
        }

        return true;
    }
}