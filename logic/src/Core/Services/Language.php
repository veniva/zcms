<?php

namespace Logic\Core\Services;

use Logic\Core\Model\Entity\Lang;

class Language
{
    protected $activeLanguages;
    protected $defaultLanguage;
    protected $currentLanguage;

    /**
     * @return mixed
     */
    public function getCurrentLanguage(): Lang
    {
        return $this->currentLanguage;
    }

    /**
     * @param mixed $currentLanguage
     */
    public function setCurrentLanguage(Lang $currentLanguage)
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