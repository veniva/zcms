<?php

namespace Logic\Tests\Core\Admin\Page;

use Doctrine\ORM\EntityManager;
use Logic\Core\Admin\Form\Page;
use Logic\Core\Admin\Page\PageUpdate;
use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Model\ListingRepository;

class PageUpdateTest extends PageBase
{
    protected $eManagerStb;
    protected $pageUpdate;
    protected $repoStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->repoStb = $this->createMock(ListingRepository::class);

        $this->eManagerStb = $this->createMock(EntityManager::class);
        $this->eManagerStb->method('getRepository')->willReturn($this->repoStb);

        $this->ctStb->method('getSelectOptions')->willReturn([]);

        $this->pageUpdate = new PageUpdate($this->transStb, $this->eManagerStb, $this->ctStb, $this->lngStb);
    }

    public function testInvalidId()
    {
        $result = $this->pageUpdate->showForm(0);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }
    
    public function testPageNotFound()
    {
        $result = $this->pageUpdate->showForm(1);

        $this->assertEquals(PageUpdate::ERR_PAGE_NOT_FOUND, $result->status);
        $this->assertEquals(PageUpdate::ERR_PAGE_NOT_FOUND_MSG, $result->message);
    }

    public function testShowSuccess()
    {
        $this->repoStb->method('findOneBy')->willReturn(new Listing());

        $result = $this->pageUpdate->showForm(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }

    public function testUpdatePageNotFound()
    {
        $result = $this->pageUpdate->update(1, [], '');

        $this->assertEquals(PageUpdate::ERR_PAGE_NOT_FOUND, $result->status);
        $this->assertEquals(PageUpdate::ERR_PAGE_NOT_FOUND_MSG, $result->message);
    }

    public function testPageUpdateInvalidForm()
    {
        $this->repoStb->method('findOneBy')->willReturn(new Listing());
        $result = $this->pageUpdate->update(1, [], '');

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertTrue(is_string($result->message) && strlen($result->message) > 0);
    }
    
    public function testPageUpdateSuccess()
    {
        $stub = new PutStb();
        $formStb = $this->createMock(Page::class);
        $formStb->method('isFormValid')->willReturn(true);
        $formStb->method('get')->willReturn($stub);
        $formStb->method('getInputFilter')->willReturn($stub);

        $this->pageUpdate->setForm($formStb);

        $this->repoStb->method('findOneBy')->willReturn(new Listing());
        $this->eManagerStb->method('find')->with(Category::class)->willReturn(new Category());

        $result = $this->pageUpdate->update(1, [], '');

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }
}

class PutStb
{
    function setValueOptions(){}
    function getValue(){}
}