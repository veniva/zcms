<?php

namespace Logic\Tests\Core\Admin\Page;

use Logic\Core\Admin\Page\PageHelpers;
use Logic\Core\Model\Entity\ListingContent;
use PHPUnit\Framework\TestCase;
use Logic\Core\Services\Language;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\Entity\Listing;

class PageHelpersTest extends TestCase
{
    public function testAddEmptyContent()
    {
        $langServiceStb = $this->createMock(Language::class);
        $langEntityStb = $this->createMock(Lang::class);

        $langEntityStb->method('getId')->willReturn(1);

        $langServiceStb->method('getActiveLanguages')->willReturn([$langEntityStb]);

        $pageHelpers = new PageHelpers($langServiceStb);
        $page = new Listing();
        $pageHelpers->addEmptyContent($page);

        $this->assertTrue($page->getContent()[0] instanceof ListingContent);
    }
}