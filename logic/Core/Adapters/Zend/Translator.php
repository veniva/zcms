<?php

namespace Logic\Core\Adapters\Zend;


use Logic\Core\Adapters\Interfaces\ITranslator;
use Zend\I18n\Translator\TranslatorInterface;

class Translator implements ITranslator
{
    /** @var TranslatorInterface */
    protected $translator;
    
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    function translate(string $value): string
    {
        return $this->translator->translate($value);
    }
}