<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\View\Helper;

use Application\View\Helper\Breadcrumb as AppBreadcrumb;
use Zend\ServiceManager\ServiceLocatorInterface;

class Breadcrumb extends AppBreadcrumb
{
    protected $template = 'helper/breadcrumb_admin';
    
    public function __construct(ServiceLocatorInterface $helperPluginManager)
    {
        parent::__construct($helperPluginManager);
    }
    
    protected function getCurrentCategory()
    {
        $serviceManager = $this->serviceManager;
        $entityManager = $serviceManager->get('entity-manager');
        $categoryContentEntity = $serviceManager->get('category-content-entity');

        $categoryContent = null;
        $request = $this->serviceManager->get('Request');
        $id = $request->getQuery('parent_id', false);
        if($id !== false)
            $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByCategory($id);
        
        return $categoryContent;
    }
}