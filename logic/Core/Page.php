<?php

namespace Logic\Core;

use Logic\Core\Model\Entity\Listing;
use Application\Service\Invokable\Language;
use Logic\Core\Interfaces\ErrorCodes;
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
        $listing = $this->em->getRepository(Listing::class)->getListingByAliasAndLang(urldecode($alias), $currentLanguageId);
        if(!$alias || !$currentLanguageId){
            return ErrorCodes::PAGE_NOT_FOUND;
        }
        
        if(!$listing){
            return ['error' => ErrorCodes::PAGE_NOT_FOUND];
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