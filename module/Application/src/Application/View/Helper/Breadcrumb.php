<?php
namespace Application\View\Helper;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\view\HelperPluginManager;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class Breadcrumb extends AbstractHelper
{
    protected $serviceManager;

    public function __construct(HelperPluginManager $serviceManager)
    {
        $this->serviceManager = $serviceManager->getServiceLocator();
    }

    public function __invoke()
    {
        $request = $this->serviceManager->get('Request');
        $route = $this->serviceManager->get('Router');
        $routeMatch = $route->match($request);
        $matchedRoute = $routeMatch->getMatchedRouteName();
        $title = '';
        $breadcrumb = $this->build($routeMatch, $title);
        $viewModel = new ViewModel([
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'matchedRoute' => $matchedRoute,
            'topRoute' => $routeMatch->getParam('alias', null) ? 'home' : $matchedRoute
        ]);
        $viewModel->setTemplate('helper/breadcrumb');
        return $this->view->render($viewModel);
    }

    public function build(RouteMatch $routeMatch, &$title = null)
    {
        $serviceManager = $this->serviceManager;
        $entityManager = $serviceManager->get('entity-manager');
        $categoryContentEntity = $serviceManager->get('category-content-entity');

        $id = $alias = $categoryContent = false;
        if($routeMatch){
            if($routeMatch->getMatchedRouteName() == 'admin/category'){
                $id = $routeMatch->getParam('id', false);
                if($id !== false)
                    $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByCategory($id);

            }elseif($routeMatch->getMatchedRouteName() == 'category'){
                $alias = $routeMatch->getParam('alias', false);
                if($alias !== false)
                    $categoryContent = $entityManager->getRepository(get_class($categoryContentEntity))->findOneByAlias(urldecode($alias));
            }
        }

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
}