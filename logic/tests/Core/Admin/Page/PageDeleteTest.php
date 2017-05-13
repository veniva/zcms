<?php

namespace Logic\Tests\Core\Admin\Page;

use Logic\Core\Admin\Page\PageDelete;
use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Listing;

class PageDeleteTest extends PageBase
{
    protected $pageDelete;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->pageDelete = new PageDelete($this->transStb, $this->emStb, $this->ctStb, $this->lngStb);
    }

    public function testNoIds()
    {
        $result = $this->pageDelete->delete('', null);

        $this->assertEquals(PageDelete::ERR_CHOOSE_ITEM, $result->status);
        $this->assertTrue(is_string($result->message) && strlen($result->message) > 0);
    }

    public function testInvalidParam()
    {
        $result = $this->pageDelete->delete('', 'e');

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testInvalidParam2()
    {
        $result = $this->pageDelete->delete('', '1,3,6,a,3');

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testInvalidId()
    {
        $result = $this->pageDelete->delete('', '1');

        $this->assertEquals(PageDelete::ERR_INVALID_ID, $result->status);
        $this->assertTrue(is_string($result->message) && strlen($result->message) > 0);
    }
    
    public function testDeleteSuccess()
    {
        $pageStb = $this->createMock(Listing::class);
        $this->emStb->method('find')->willReturn($pageStb);

        $result = $this->pageDelete->delete('', '1');

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(is_string($result->message) && strlen($result->message) > 0);
    }
}