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
use Zend\Validator\Exception\InvalidArgumentException;

class Extension extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const FALSE_EXTENSION = 'fileExtensionFalse';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::FALSE_EXTENSION => "File is of incorrect type. Allowed are: %extensions%",
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'extensions' => ['options' => 'extensions_formatted'],
    ];

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = [
        'extensions' => ['png', 'jpeg', 'gif', 'jpg'],      // List of extensions
        'extensions_formatted'
    ];

    public function __construct($options = null)
    {
        $this->options['extensions_formatted'] = str_replace('|', ', ', $this->getExtensions());
        parent::__construct($options);
    }

    /**
     * @return string Pipe | delimited string eg. png|gif|...
     */
    protected function getExtensions()
    {
        $extensions = $this->options['extensions'];
        $type = gettype($extensions);

        if($type != 'array' && $type != 'string')
            throw new InvalidArgumentException('Extensions option must be an array or comma delimited string values');

        if($type == 'array')
            return implode('|', $this->options['extensions']);
        else
            return preg_replace('/\s+/', '', str_replace(',', '|', $extensions));
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
        if(!preg_match('/('.$this->getExtensions().')/i', strrchr($value, '.'))){
            $this->error(self::FALSE_EXTENSION);
            return false;
        }
        return true;
    }
}