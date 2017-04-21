<?php

namespace Logic\Tests\Core\Admin\Page;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Page\PageList;
use Logic\Core\Interfaces\StatusCodes;
use PHPUnit\Framework\TestCase;

class PageListTest extends TestCase
{
    protected $emStb;
    protected $transStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->emStb->method('getRepository')->willReturn(new PltStb());
        $this->transStb = $this->createMock(ITranslator::class);
    }

    public function testInvalidArg()
    {
        $pageList = new PageList($this->emStb, $this->transStb);

        $this->expectException('TypeError');
        $pageList->showList();
    }

    public function testListPageSuccess()
    {


        $pageList = new PageList($this->emStb, $this->transStb);
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