<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Service\Invokable;

use Application\Model\Entity\Lang;
use Zend\ServiceManager\ServiceLocatorInterface;

class Misc
{
    /**
     * @var ServiceLocatorInterface
     */
    protected static $staticServiceLocator;
    protected static $staticRoute;
    protected static $currentLanguage;
    protected static $currentLanguageId;
    protected static $defaultLangID;
    protected static $defaultLanguage;
    protected static $activeLanguages;

    /**
     * @return mixed
     */
    public static function getStaticRoute()
    {
        return self::$staticRoute;
    }

    /**
     * @param mixed $route
     */
    public static function setStaticRoute($route)
    {
        self::$staticRoute = $route;
    }

    public static function setStaticServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        self::$staticServiceLocator = $serviceLocator;
    }

    public static function getStaticServiceLocator()
    {
        return self::$staticServiceLocator;
    }

    public static function getAdminEmail()
    {
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $userEntity = self::getStaticServiceLocator()->get('user-entity');

        $admin = $entityManager->getRepository(get_class($userEntity))->findOneById(1);
        return $admin ? $admin->getEmail() : null;
    }

    public static function setActiveLanguages($activeLanguages)
    {
        self::$activeLanguages = $activeLanguages;
    }
    /**
     * @deprecated Use getActiveLanguages() instead
     */
    public static function getActiveLangs()
    {
        return self::$activeLanguages;
    }

    public static function getActiveLanguages()
    {
        return self::$activeLanguages;
    }

    /**
     * @deprecated
     */
    public static function getActiveLangsArray()
    {
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $languageEntity = self::getStaticServiceLocator()->get('lang-entity');
        $languages = $entityManager->getRepository(get_class($languageEntity))->getActiveLangs();//v_todo - also remove the repo class
        return $languages;
    }

    /**
     * @deprecated Use getCurrentLanguage() instead
     */
    public static function getCurrentLang()
    {
        return self::$currentLanguage;
    }

    /**
     * @return \Application\Model\Entity\Lang
     */
    public static function getCurrentLanguage()
    {
        return self::$currentLanguage;
    }

    /**
     * @param Lang $currentLanguage Either the matched entity, or new (empty) entity
     */
    public static function setCurrentLanguage($currentLanguage)
    {
        self::$currentLanguage = $currentLanguage;
        self::$currentLanguageId = $currentLanguage->getId();
    }

    /**
     * @deprecated Use Misc::getCurrentLanguage()->getId() instead
     */
    public static function getLangID()
    {
        return self::$currentLanguageId;
    }

    /**
     * @return \Application\Model\Entity\Lang
     */
    public static function getDefaultLanguage()
    {
        return self::$defaultLanguage;
    }

    public static function setDefaultLanguage($language)
    {
        self::$defaultLanguage = $language;
        self::$defaultLangID = $language->getId();
    }

    /**
     * @deprecated Use getDefaultLanguage()->getId() instead
     */
    public static function getDefaultLanguageID()
    {
        return self::$defaultLangID;
    }

    public static function alias($str) {
        $str = preg_replace('/[\s]+/i', '-', str_replace(',', '', trim($str)));
        $alias = extension_loaded('mbstring') ? mb_strtolower($str) : strtolower($str);
        return $alias;
    }
}
