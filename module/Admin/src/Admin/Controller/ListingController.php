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
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        $listingRepository = $entityManager->getRepository(get_class($listingEntity));

        $listingsPaginated = $listingRepository->getListingsPaginated($parentCategory);
        $listingsPaginated->setCurrentPageNumber($page);

        $categories = $categoryTree->getCategories();

        return [
            'title' => 'Pages',
            'listings' => $listingsPaginated,
            'categories' => $categories,
            'parentCategory' => $parentCategory,
            'page' => $page,
        ];
    }
}
