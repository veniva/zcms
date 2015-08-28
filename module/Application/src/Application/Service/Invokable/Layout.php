<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 12:29
 */

namespace Application\Service\Invokable;


use Application\Model\LangTable;
use Zend\ServiceManager\ServiceLocatorInterface;

class Layout
{
    /**
     * @var ServiceLocatorInterface
     */
    protected static $staticServiceLocator;

    public static function setStaticServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        self::$staticServiceLocator = $serviceLocator;
    }

    public static function getStaticServiceLocator()
    {
        return self::$staticServiceLocator;
    }

    public static function getAllLangs()
    {
        $langs = new LangTable();
        return $langs->getAllLangs();
    }

    public static function getTopCategories()
    {
        $entityManager = self::getStaticServiceLocator()->get('entity-manager');
        $categoryEntity = self::getStaticServiceLocator()->get('category-entity');
        return $entityManager->getRepository(get_class($categoryEntity))->getAllTopCategories();
    }
}