<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 12:29
 */

namespace Application\Service\Invokable;


use Application\Model\Category;
use Application\Model\LangTable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Layout implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getAllLangs()
    {
        $langs = new LangTable();
        return $langs->getAllLangs();
    }

    public static function getTopCategories()
    {
        $category = new Category();
        return $category->getTopCategories();
    }
}