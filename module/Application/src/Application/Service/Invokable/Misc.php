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

    public static function alias($str) {
        $str = preg_replace('/[\s]+/i', '-', str_replace(',', '', trim($str)));
        $alias = extension_loaded('mbstring') ? mb_strtolower($str) : strtolower($str);
        return $alias;
    }
}
