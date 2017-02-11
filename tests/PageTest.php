<?php

namespace Tests;
use Doctrine\ORM\EntityManager;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\ListingRepository;
use Logic\Core\Page;
use Logic\Core\Services\Language;
use PHPUnit\Framework\TestCase;
use Logic\Core\Model\Entity\Listing;

class PageTest extends TestCase
{
    public function testReturnsData()
    {
        $emStub = $repoStub = $listingEntityStub = $languageServiceStub = $langEntityStub = null;
        $this->createStubs($emStub, $repoStub, $listingEntityStub, $languageServiceStub, $langEntityStub);
        $repoStub->method('getListingByAliasAndLang')->willReturn($listingEntityStub);
        $langEntityStub->method('getId')->willReturn(1);

        $page = new Page($emStub, $languageServiceStub);
        $result = $page->getShowData('some');

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('error', $result);
        $this->assertFalse($result['error']);
    }

    public function testPageNotFound()
    {
        $emStub = $repoStub = $listingEntityStub = $languageServiceStub = $langEntityStub = null;
        $this->createStubs($emStub, $repoStub, $listingEntityStub, $languageServiceStub, $langEntityStub);
        $repoStub->method('getListingByAliasAndLang')->willReturn($listingEntityStub);
        $langEntityStub->method('getId')->willReturn(0);

        $page = new Page($emStub, $languageServiceStub);

        $result = $page->getShowData('');
        $this->assertEquals(StatusCodes::PAGE_NOT_FOUND, $result['error']);

        $result = $page->getShowData('some');
        $this->assertEquals(StatusCodes::PAGE_NOT_FOUND, $result['error']);
    }

    public function testNoListing()
    {
        $emStub = $repoStub = $listingEntityStub = $languageServiceStub = $langEntityStub = null;
        $this->createStubs($emStub, $repoStub, $listingEntityStub, $languageServiceStub, $langEntityStub);
        $repoStub->method('getListingByAliasAndLang')->willReturn(null);
        $langEntityStub->method('getId')->willReturn(1);

        $page = new Page($emStub, $languageServiceStub);

        $result = $page->getShowData('some');
        $this->assertEquals(StatusCodes::PAGE_NOT_FOUND, $result['error']);
    }

    protected function createStubs(&$emStub, &$repoStub, &$listingEntityStub, &$languageServiceStub, &$langEntityStub)
    {
        $emStub = $this->createMock(EntityManager::class);
        $repoStub = $this->createMock(ListingRepository::class);
        $listingEntityStub = $this->createMock(Listing::class);
        $languageServiceStub = $this->createMock(Language::class);
        $langEntityStub = $this->createMock(Lang::class);

        $emStub->method('getRepository')->willReturn($repoStub);

        $listingEntityStub->method('getSingleListingContent')->willReturn(true);
        $listingEntityStub->method('getListingImage')->willReturn(true);
        $languageServiceStub->method('getCurrentLanguage')->willReturn($langEntityStub);
    }
}