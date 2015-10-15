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
            return;
        }
        $listingContentEntity = $this->serviceLocator->get('listing-content-entity');
        $entityManager = $this->serviceLocator->get('entity-manager');

        $repository = $entityManager->getRepository(get_class($listingContentEntity));
        $listingContent = $repository->findOneBy(['alias' => $alias, 'langId' => Misc::getLangID()]);
        if(!$listingContent) $this->getResponse()->setStatusCode(404);

        return new ViewModel([
            'listing_content' => $listingContent
        ]);
    }
}
