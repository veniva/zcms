<?php

namespace Tests\Admin\Page;


use Logic\Core\Admin\Form\Page;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Model\Entity\ListingImage;

class PageBaseTest extends PageBase
{
    protected $basePage;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->basePage = new PageBaseExt($this->transStb, $this->emStb, $this->ctStb, $this->lngStb);
    }
    
    public function testSetAliasByAlias()
    {
        $data = $this->basePage->setAlias([
            'content' => [
                ['alias' => 'some alias']
            ]
        ]);

        $this->assertEquals('some-alias', $data['content'][0]['alias']);
    }

    public function testSetAliasByTitle()
    {
        $tData = [
            'content' => [
                ['title' => 'some title']
            ]
        ];

        $data = $this->basePage->setAlias($tData);

        $this->assertEquals('some-title', $data['content'][0]['alias']);
    }
    
    public function testPrepareFormInvalid()
    {
        $formStb = $this->createMock(Page::class);
        $formStb->method('validateBase64Image')->willReturn(false);
        $formStb->method('get')->willReturn(new PbtStb());
        $this->basePage->setForm($formStb);
        
        $page = new Listing();
        $data = [
            'listing_image' => ['base64' => 'some', 'name' => 'some']
        ];
        $result = $this->basePage->prepareForm($page, $data);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertTrue($result->has('form'));
        $this->assertTrue($result->has('page'));
    }

    public function testPrepareFormSuccess()
    {
        $formStb = $this->createMock(Page::class);
        $formStb->method('validateBase64Image')->willReturn(true);
        $formStb->method('get')->willReturn(new PbtStb());
        $this->basePage->setForm($formStb);

        $page = new Listing();
        $data = [
            'listing_image' => ['base64' => 'some', 'name' => 'some']
        ];
        $result = $this->basePage->prepareForm($page, $data);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }

    public function testAddPageImage()
    {
        $imageName = 'some name';
        $page = new Listing();
        $this->basePage->addPageImage($page, [
            'listing_image' => [
                'name' => $imageName
            ]
        ]);

        $this->assertTrue($page->getListingImage() instanceof ListingImage);
        $this->assertEquals($page->getListingImage()->getImageName(), $imageName);
    }
}

class PbtStb
{
    function setValueOptions(){}
    function getValue(){}
}