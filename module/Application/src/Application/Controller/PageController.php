<?php

namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController
{
    public function showAction()
    {
        $alias = $this->params()->fromRoute('alias', null);
        if(!$alias){
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $listingContentEntity = $this->serviceLocator->get('listing-content-entity');
        $entityManager = $this->serviceLocator->get('entity-manager');

        $repository = $entityManager->getRepository(get_class($listingContentEntity));
        $listingContent = $repository->findOneByAlias($alias);

        return new ViewModel([
            'listing_content' => $listingContent
        ]);
    }
}
