<?php

namespace Logic\Core;

use Logic\Core\Model\Entity\Listing;
use Logic\Core\Services\Language;
use Veniva\Lbs\Interfaces\StatusCodes;
use Doctrine\ORM\EntityManager;

class Page
{
    /** @var Language  */
    protected $language;
    
    protected $em;

    public function __construct(EntityManager $entityManager, Language $languageService)
    {
        $this->language = $languageService;
        $this->em = $entityManager;
    }

    public function getShowData(string $alias) :array
    {
        $currentLanguageId = $this->language->getCurrentLanguage()->getId();
        if(!$alias || !$currentLanguageId){
            return [
                'error' => StatusCodes::PAGE_NOT_FOUND
            ];
        }
        
        $listing = $this->em->getRepository(Listing::class)->getListingByAliasAndLang(urldecode($alias), $currentLanguageId);
        if(!$listing){
            return ['error' => StatusCodes::PAGE_NOT_FOUND];
        }
        $listingContent = $listing->getSingleListingContent($currentLanguageId);
        $listingImage = $listing->getListingImage();

        return [
            'error' => false,
            'listing_content' => $listingContent,
            'listing' => $listing,
            'listing_image' => $listingImage
        ];
    }
}