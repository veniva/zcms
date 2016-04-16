<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\View\Helper\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Breadcrumb implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        return new \Admin\View\Helper\Breadcrumb($helperPluginManager);
    }
}