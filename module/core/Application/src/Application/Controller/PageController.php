<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;

use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Page;
use Zend\Mvc\Controller\AbstractActionController;
use Interop\Container\ContainerInterface;
use Zend\View\Model\ViewModel;
use Application\ServiceLocatorAwareTrait;

class PageController extends AbstractActionController
{
    use ServiceLocatorAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function showAction()
    {
        $params = $this->params();
        $alias = $params->fromRoute('alias', null);
        $pageLogic = new Page($this->serviceLocator->get('entity-manager'), $this->getServiceLocator()->get('language'));
        $data = $pageLogic->getShowData($alias);
        if($data['error'] === StatusCodes::PAGE_NOT_FOUND){
            $this->getResponse()->setStatusCode(404);
            return [];
        }

        $listingContent = $data['listing_content'];
        $listingImage = $data['listing_image'];
        $this->layout()->setVariables([
            'meta_title' => $listingContent ? $listingContent->getMetaTitle() : null,
            'meta_description' => $listingContent ? $listingContent->getMetaDescription() : null,
            'meta_keywords' => $listingContent ? $listingContent->getMetaKeywords() : null,
        ]);

        return new ViewModel([
            'listing' => $data['listing'],
            'content' => $listingContent,
            'thumbnail' => $listingImage ? $listingImage->getImageName() : null
        ]);
    }
}
