<?php

namespace Application\Service\Invokable;


class Language
{
    protected $activeLanguages;

    protected $defaultLanguage;

    /**
     * @return mixed
     */
    public function getActiveLanguages()
    {
        return $this->activeLanguages;
    }

    /**
     * @param mixed $activeLanguages
     */
    public function setActiveLanguages($activeLanguages)
    {
        $this->activeLanguages = $activeLanguages;
    }

    /**
     * @return \Application\Model\Entity\Lang
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * @param mixed $defaultLanguage
     */
    public function setDefaultLanguage($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;
    }
}