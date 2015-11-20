<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;

use Application\Service\Invokable\Misc;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController
{
    public function showAction()
    {
        $params = $this->params();
        $alias = $params->fromRoute('alias', null);
        if(!$alias || !Misc::getCurrentLanguage()){
            $this->getResponse()->setStatusCode(404);
            return [];
        }
        $listingEntity = $this->serviceLocator->get('listing-entity');
        $entityManager = $this->serviceLocator->get('entity-manager');

        $listing = $entityManager->getRepository(get_class($listingEntity))->getListingByAliasAndLang(urldecode($alias), Misc::getCurrentLanguage()->getId());
        if(!$listing){
            $this->getResponse()->setStatusCode(404);
            return [];
        }
        $metaData = $listing->getSingleMetadata(Misc::getCurrentLanguage()->getId());
        if(!$metaData) $metaData = $listing->getSingleMetadata(Misc::getDefaultLanguage()->getId());

        $this->layout()->setVariables([
            'meta_title' => $metaData ? $metaData->getMetaTitle() : null,
            'meta_description' => $metaData ? $metaData->getMetaDescription() : null,
            'meta_keywords' => $metaData ? $metaData->getMetaKeywords() : null,
        ]);
        $listingImage = $listing->getListingImage();

        return new ViewModel([
            'listing' => $listing,
            'thumbnail' => $listingImage ? $listingImage->getImageName() : null
        ]);
    }
}
