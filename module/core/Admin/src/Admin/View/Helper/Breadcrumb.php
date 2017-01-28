<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\View\Helper;

use Logic\Core\Model\Entity\CategoryContent;
use Logic\Core\Model\Entity\Lang;
use Application\View\Helper\Breadcrumb as AppBreadcrumb;

class Breadcrumb extends AppBreadcrumb
{
    protected $template = 'helper/breadcrumb_admin';
    
    public function __construct(CategoryContent $currentCategoryContent = null, Lang $defaultLanguage)
    {
        parent::__construct($currentCategoryContent, $defaultLanguage);
    }
}