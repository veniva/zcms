<?php

namespace Tests\Admin\Page;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Page\PageList;
use Logic\Core\Interfaces\StatusCodes;
use PHPUnit\Framework\TestCase;

class PageListTest extends TestCase
{
    public function testListPageSuccess()
    {
        $emStb = $this->createMock(EntityManager::class);
        $emStb->method('getRepository')->willReturn(new PltStb());
        $transStb = $this->createMock(ITranslator::class);

        $pageList = new PageList($emStb, $transStb);
        $result = $pageList->showList(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(is_array($result->get('pages')));
        $this->assertTrue(is_object($result->get('pages_paginated')));
    }
}

class PltStb
{
    function getListingsPaginated(){ return $this; }
    function setCurrentPageNumber(){}
}