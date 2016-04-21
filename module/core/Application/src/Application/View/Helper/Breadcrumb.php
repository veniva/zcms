<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\View\Helper;

use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use Doctrine\Common\Collections\Collection;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class Breadcrumb extends AbstractHelper
{
    protected $template = 'helper/breadcrumb';

    /** @var  CategoryContent */
    protected $currentCategoryContent;
    /** @var Lang  */
    protected $defaultLanguage;

    /**
     * Breadcrumb constructor.
     * @param CategoryContent|null $currentCategoryContent
     * @param Lang $defaultLanguage
     */
    public function __construct(CategoryContent $currentCategoryContent = null, Lang $defaultLanguage)
    {
        $this->currentCategoryContent = $currentCategoryContent;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * @param $section - either "admin" or null
     * @return string
     */
    public function __invoke($section = null)
    {
        $title = '';
        $breadcrumb = $this->build($title);
        $viewModel = new ViewModel([
            'breadcrumb' => $breadcrumb,
            'title' => $title
        ]);

        $viewModel->setTemplate($this->template);
        return $this->view->render($viewModel);
    }

    public function build(&$title = null)
    {
        $defaultLangId = $this->defaultLanguage->getId();
        $categoryContent = $this->currentCategoryContent;
        if(!$categoryContent) return [];

        $parentCategories = $categoryContent->getCategory()->getParents();
        $title = $categoryContent->getTitle();

        $parentsArranged = $this->sortParents($parentCategories);

        $aBcrumb[] = '';//the Top category
        foreach($parentsArranged as $parentCategory){
            if(!$parentCategory->getId())
                break;

            $parentCategoryContent = $parentCategory->getSingleCategoryContent($defaultLangId);

            if($parentCategoryContent instanceof CategoryContent){
                if($categoryContent->getAlias() != $parentCategoryContent->getAlias())
                    $aBcrumb[] = [
                        'id'    => $parentCategory->getId(),
                        'alias' => $parentCategoryContent->getAlias(),
                        'title' => $parentCategoryContent->getTitle(),
                    ];
            }
        }

        return $aBcrumb;
    }

    /**
     * Sort the parent Categories by the top first order
     * @param Collection $parentCategories The collection of parent categories
     * @param null|int $next
     * @return array
     */
    protected function sortParents($parentCategories, $next = null)
    {
        static $parentsArranged = [];
        foreach($parentCategories as $parentCategory){
            $parentOfParent = $parentCategory->getParent();
            if($parentOfParent == $next){
                $parentsArranged[] = $parentCategory;
                $this->sortParents($parentCategories, $parentCategory->getId());
            }
        }
        return $parentsArranged;
    }
}