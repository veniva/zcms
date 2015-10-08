<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 28/08/2015
 * Time: 13:46
 */

namespace Application\Service\Invokable;

use Zend\ServiceManager\ServiceLocatorInterface;

class Misc
{
    /**
     * @var ServiceLocatorInterface
     */
    protected static $staticServiceLocator;

    protected static $staticRoute;

    protected static $langID;

    protected static $defaultLangID;

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

    public static function setLangID()
    {
        $langISO = self::$staticRoute->getParam('lang', 'en');
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $languageEntity = self::getStaticServiceLocator()->get('lang-entity');
        $language = $entityManager->getRepository(get_class($languageEntity))->findOneByIsoCode($langISO);
        self::$langID = $language->getId();
    }

    public static function getLangID()
    {
        return self::$langID;
    }

    public static function setDefaultLanguage()
    {
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $languageEntity = self::getStaticServiceLocator()->get('lang-entity');
        $language = $entityManager->getRepository(get_class($languageEntity))->findOneByStatus(2);
        self::$defaultLangID = $language->getId();
    }

    public static function getDefaultLanguageID()
    {
        return self::$defaultLangID;
    }
}
