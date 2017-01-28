<?php

namespace Application\Service\Invokable;


class Language
{
    protected $activeLanguages;
    protected $defaultLanguage;
    protected $currentLanguage;

    /**
     * @return mixed
     */
    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }

    /**
     * @param mixed $currentLanguage
     */
    public function setCurrentLanguage($currentLanguage)
    {
        $this->currentLanguage = $currentLanguage;
    }

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
     * @return \Logic\Core\Model\Entity\Lang
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