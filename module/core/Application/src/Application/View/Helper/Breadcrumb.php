<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\View\Helper;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class Breadcrumb extends AbstractHelper
{
    /** @var  ServiceLocatorInterface */
    protected $serviceManager;
    protected $template = 'helper/breadcrumb';
    /** @var  RouteMatch */
    protected $routeMatch;

    /**
     * Breadcrumb constructor.
     * @param ServiceLocatorInterface $helperPluginManager NOTE: This argument is of type
     * Zend\view\HelperPluginManager which is injected by the factory
     */
    public function __construct(ServiceLocatorInterface $helperPluginManager)
    {
        $this->serviceManager = $helperPluginManager->getServiceLocator();
        $request = $this->serviceManager->get('Request');
        $route = $this->serviceManager->get('Router');
        $this->routeMatch = $route->match($request);
    }

    /**
     * @param $section - either "admin" or null
     * @return string
     */
    public function __invoke($section = null)
    {

        $matchedRoute = $this->routeMatch->getMatchedRouteName();
        $title = '';
        $breadcrumb = $this->build($title);
        $viewModel = new ViewModel([
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'matchedRoute' => $matchedRoute,
            'topRoute' => $this->routeMatch->getParam('alias', null) ? 'home' : $matchedRoute
        ]);

        $viewModel->setTemplate($this->template);
        return $this->view->render($viewModel);
    }

    public function build(&$title = null)
    {
        $serviceManager = $this->serviceManager;
        $categoryContent = $this->getCurrentCategory();
        if(!$categoryContent) return [];

        $categoryParents = $categoryContent->getCategory()->getParents();
        $title = $categoryContent->getTitle();

        $aBcrumb[] = '';//the Top category
        foreach($categoryParents as $categoryParent){
            if(!$categoryParent->getId())
                break;

            $parentCategoryContent = $categoryParent->getSingleCategoryContent($serviceManager->get('language')->getDefaultLanguage()->getId());

            if($categoryContent->getAlias() != $parentCategoryContent->getAlias())
                $aBcrumb[] = [
                    'alias' => $parentCategoryContent->getAlias(),
                    'title' => $parentCategoryContent->getTitle(),
                    'id'    => $parentCategoryContent->getCategory()->getId(),
                ];
        }

        return $aBcrumb;
    }

    protected function getCurrentCategory()
    {
        $serviceManager = $this->serviceManager;
        $entityManager = $serviceManager->get('entity-manager');
        $categoryContentEntity = $serviceManager->get('category-content-entity');

        $categoryContent = null;
        $alias = $this->routeMatch->getParam('alias', false);
        if($alias !== false)
            $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias(urldecode($alias));

        return $categoryContent;
    }
}