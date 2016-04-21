<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController
{
    use ServiceLocatorAwareTrait;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function showAction()
    {
        $params = $this->params();
        $alias = $params->fromRoute('alias', null);
        $languageService = $this->getServiceLocator()->get('language');
        $currentLanguageId = $languageService->getCurrentLanguage()->getId();
        if(!$alias || !$currentLanguageId){
            $this->getResponse()->setStatusCode(404);
            return [];
        }
        $listingEntity = $this->serviceLocator->get('listing-entity');
        $entityManager = $this->serviceLocator->get('entity-manager');

        $listing = $entityManager->getRepository(get_class($listingEntity))->getListingByAliasAndLang(urldecode($alias), $currentLanguageId);
        if(!$listing){
            $this->getResponse()->setStatusCode(404);
            return [];
        }
        $listingContent = $listing->getSingleListingContent($currentLanguageId);

        $this->layout()->setVariables([
            'meta_title' => $listingContent ? $listingContent->getMetaTitle() : null,
            'meta_description' => $listingContent ? $listingContent->getMetaDescription() : null,
            'meta_keywords' => $listingContent ? $listingContent->getMetaKeywords() : null,
        ]);
        $listingImage = $listing->getListingImage();

        return new ViewModel([
            'listing' => $listing,
            'content' => $listingContent,
            'thumbnail' => $listingImage ? $listingImage->getImageName() : null
        ]);
    }
}
