<?php

namespace Admin\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;

class ListingController extends AbstractActionController
{
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function listAction()
    {
        $parentCategory = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $listingEntity = $this->getServiceLocator()->get('listing-entity');
        $listingRepository = $entityManager->getRepository(get_class($listingEntity));

        $listingsPaginated = $listingRepository->getListingsPaginated($parentCategory);
        $listingsPaginated->setCurrentPageNumber($page);

        return [
            'title' => 'Pages',
            'listings' => $listingsPaginated,
            'parentCategory' => $parentCategory,
            'page' => $page,
        ];
    }
}
