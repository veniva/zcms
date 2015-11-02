<?php

namespace Admin\Controller;


use Admin\Form\Listing as ListingForm;
use Application\Service\Invokable\Misc;
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
        $parentCategory = $this->params()->fromRoute('id');
        $page = $this->params()->fromRoute('page');
        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);

        $listingsPaginated = $listingRepository->getListingsPaginated($parentCategory);
        $listingsPaginated->setCurrentPageNumber($page);

        $categories = $categoryTree->getCategories();

        return [
            'title' => 'Pages',
            'listings' => $listingsPaginated,
            'categories' => $categories,
            'parentCategory' => $parentCategory,
            'page' => $page,
            'categoryTree' => $categoryTree,
        ];
    }

    public function editAction()
    {
        $categId = $this->params()->fromRoute('id');
        $page = $this->params()->fromRoute('page');
        $parentFilter = $this->params()->fromRoute('filter');
        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);

        $listing = $listingRepository->findOneBy(['id' => $categId]);
        $listingContentDefaultLanguage = $listing->getContent();

        $languages = Misc::getActiveLangs();
        $formClass = new ListingForm($listingContentDefaultLanguage, $languages,
            $this->getServiceLocator()->get('translator'), $this->getServiceLocator()->get('validator-messages'));

        $form = $formClass->getForm();
        $form->bind($listingContentDefaultLanguage);

        //set metadata content on the default language
        $listingMetadataContent = $listing->getMetadata();
        foreach(['metaTitle', 'metaDescription', 'metaKeywords'] as $input){
            $form->get($input)->setValue($listingMetadataContent->{'get'.$input}());
        }

        $listingContent = [Misc::getDefaultLanguage()->getIsoCode() => $listingContentDefaultLanguage];
        $listingMeta = [Misc::getDefaultLanguage()->getIsoCode() => $listingMetadataContent];
        foreach($languages as $language){
            if($language->getId() != Misc::getDefaultLanguage()->getId()){
                $listingContentLanguage = $listing->getContent($language->getId());
                if(get_class($listingContentLanguage) == get_class($listingContentDefaultLanguage)){//if content on that language exists
                    $listingContent[$language->getIsoCode()] = $listingContentLanguage;
                    foreach(['link', 'alias', 'title', 'text'] as $input){
                        $form->get($input.'_'.$language->getIsoCode())->setValue($listingContentLanguage->{'get'.$input}());
                    }

                    //set metadata fields
                    $listingMetadataLanguage = $listing->getMetadata($language->getId());
                    if(get_class($listingMetadataLanguage) == get_class($listingMetadataContent)){
                        $listingMeta[$language->getIsoCode()] = $listingMetadataLanguage;
                        foreach(['metaTitle', 'metaDescription', 'metaKeywords'] as $input){
                            $form->get($input.'_'.$language->getIsoCode())->setValue($listingMetadataLanguage->{'get'.$input}());
                        }
                    }

                }
            }
        }

        return [
            'page' => $page,
            'filter' => $parentFilter,
            'form' => $form,
            'categoryTree' => $categoryTree,//v_todo - create multiple parent categories support
            'parentCategory' => $listing->getCategories()[0]->getId(),//work with one parent category for the time being
            'listing' => $listing,
            'action' => 'Edit',
        ];
    }

    protected function dependencyProvider(&$entityManager, &$listingEntity, &$categoryTree, &$listingRepository)
    {
        /* \Doctrine\Orm\EntityManager */
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        /* \Application\Model\Entity\Listing */
        $listingEntity = $this->getServiceLocator()->get('listing-entity');
        /* \Admin\CategoryTree\CategoryTree $categoryTree */
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        /* @var \Application\Model\ListingRepository $listingRepository */
        $listingRepository = $entityManager->getRepository(get_class($listingEntity));
    }
}
