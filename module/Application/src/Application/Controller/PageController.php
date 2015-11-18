<?php

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
        if(!$alias){
            $this->getResponse()->setStatusCode(404);
            return [];
        }
        $listingEntity = $this->serviceLocator->get('listing-entity');
        $entityManager = $this->serviceLocator->get('entity-manager');

        $listing = $entityManager->getRepository(get_class($listingEntity))->getListingByAlias(urldecode($alias), Misc::getCurrentLang()->getId());
        if(!$listing){
            $this->getResponse()->setStatusCode(404);
            return [];
        }
        $content = $listing->getSingleListingContent(Misc::getCurrentLang()->getId());
        $metaData = $listing->getSingleMetadata(Misc::getCurrentLang()->getId());
        $this->layout()->setVariables([
            'meta_title' => $metaData->getMetaTitle(),
            'meta_description' => $metaData->getMetaDescription(),
            'meta_keywords' => $metaData->getMetaKeywords(),
        ]);

        return new ViewModel([
            'listing_content' => $content
        ]);
    }
}
