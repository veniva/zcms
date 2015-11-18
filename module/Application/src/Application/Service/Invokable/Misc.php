<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Service\Invokable;

use Application\Model\Entity\Lang;
use Doctrine\Common\Collections\Criteria;
use Zend\ServiceManager\ServiceLocatorInterface;

class Misc
{
    /**
     * @var ServiceLocatorInterface
     */
    protected static $staticServiceLocator;
    protected static $staticRoute;
    protected static $lang;
    protected static $langID;
    protected static $defaultLangID;
    protected static $defaultLang;
    protected static $languages;

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
        return $admin->getEmail();
    }

    public static function setActiveLangs()
    {
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $languageEntity = self::getStaticServiceLocator()->get('lang-entity');
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->gt('status', 0));
        self::$languages = $entityManager->getRepository(get_class($languageEntity))->matching($criteria);
    }

    public static function getActiveLangs()
    {
        return self::$languages;
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

    public static function setLangID()
    {
        $langISO = self::$staticRoute->getParam('lang', 'en');
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $languageEntity = self::getStaticServiceLocator()->get('lang-entity');
        $language = $entityManager->getRepository(get_class($languageEntity))->findOneByIsoCode($langISO);
        self::$lang = $language;
        self::$langID = $language->getId();
    }

    /**
     * @return \Application\Model\Entity\Lang
     */
    public static function getCurrentLang()
    {
        return self::$lang;
    }

    /**
     * @deprecated Use Misc::getCurrentLang()->getId() instead
     */
    public static function getLangID()
    {
        return self::$langID;
    }

    public static function setDefaultLanguage()
    {
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $languageEntity = self::getStaticServiceLocator()->get('lang-entity');
        $language = $entityManager->getRepository(get_class($languageEntity))->findOneByStatus(Lang::STATUS_DEFAULT);
        self::$defaultLangID = $language->getId();
        self::$defaultLang = $language;
    }

    /**
     * @return \Application\Model\Entity\Lang
     */
    public static function getDefaultLanguage()
    {
        return self::$defaultLang;
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
