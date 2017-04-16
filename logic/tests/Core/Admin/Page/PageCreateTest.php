<?php

namespace Tests\Core\Admin\Page;

use Logic\Core\Admin\Form\Page;
use Logic\Core\Admin\Page\PageCreate;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Interfaces\StatusCodes;

class PageCreateTest extends PageBase
{
    protected $pageCreate;
    
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->pageCreate = new PageCreate($this->transStb, $this->emStb, $this->ctStb, $this->lngStb);
    }

    public function testNoCategs()
    {
        $this->pbStb->method('countAll')->willReturn(0);

        $result = $this->pageCreate->verifyCategory();

        $this->assertEquals(PageCreate::ERR_NO_CATEGORY, $result->status);
        $this->assertEquals(PageCreate::ERR_NO_CATEGORY_MSG, $result->message);
    }

    public function testShowSuccess()
    {
        $this->pbStb->method('countAll')->willReturn(1);
        $this->ctStb->method('getSelectOptions')->willReturn([]);

        $result = $this->pageCreate->showForm();

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->has('form'));
        $this->assertTrue($result->get('page') instanceof Listing);
        $this->assertTrue($result->has('active_languages'));
    }

    public function testCreateInvalidForm()
    {
        $formStb = $this->createMock(Page::class);
        $formStb->method('isFormValid')->willReturn(false);
        $formStb->method('get')->willReturn(new PctStb());
        $this->pageCreate->setForm($formStb);
        $this->pbStb->method('countAll')->willReturn(1);

        $result = $this->pageCreate->create([], '');

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertTrue(is_string($result->message) && !empty($result->message));
        $this->assertTrue($result->has('form'));
        $this->assertTrue($result->has('page'));
    }

    public function testCreateSuccess()
    {
        $formStb = $this->createMock(Page::class);
        $formStb->method('isFormValid')->willReturn(true);
        $pctStb = new PctStb();
        $formStb->method('get')->willReturn($pctStb);
        $formStb->method('getInputFilter')->willReturn($pctStb);
        $this->emStb->method('find')->willReturn(new Category());

        $this->pageCreate->setForm($formStb);
        $this->pbStb->method('countAll')->willReturn(1);

        $result = $this->pageCreate->create([], '');

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(is_string($result->message) && !empty($result->message));
    }
}

class PctStb
{
    function setValueOptions(){}
    function getValue(){}
}