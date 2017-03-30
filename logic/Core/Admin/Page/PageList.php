<?php

namespace Logic\Core\Admin\Page;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Model\ListingRepository;
use Logic\Core\Result;

class PageList extends BaseLogic
{
    /** @var EntityManager */
    protected $em;
    
    public function __construct(EntityManager $em, ITranslator $translator)
    {
        $this->em = $em;
        
        parent::__construct($translator);
    }
    
    public function showList($langId, $parentId = 0, $page = 1): Result
    {
        /** @var ListingRepository $repo */
        $repo = $this->em->getRepository(Listing::class);
        $pagesPaginated = $repo->getListingsPaginated($parentId);
        $pagesPaginated->setCurrentPageNumber($page);

        $i = 0;
        $pagesData = [];
        foreach($pagesPaginated as $listing){
            $pagesData[$i]['id'] = $listing->getId();
            $pagesData[$i]['sort'] = $listing->getSort();
            $pagesData[$i]['link'] = $listing->getSingleListingContent($langId)->getLink();
            $n = 0;
            $categories = [];
            foreach($listing->getCategories() as $category){
                $categories[$n]['id'] = $category->getId();
                $categories[$n]['title'] = $category->getSingleCategoryContent($langId)->getTitle();
                $n++;
            }
            $pagesData[$i]['categories'] = $categories;
            $i++;
        }

        return $this->result(StatusCodes::SUCCESS, null, [
            'pages' => $pagesData,
            'pages_paginated' => $pagesPaginated
        ]);
    }
}