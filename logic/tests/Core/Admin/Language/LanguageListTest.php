<?php

namespace Logic\Tests\Core\Admin\Language;

use Logic\Core\Admin\Language\LanguageList;
use Veniva\Lbs\Interfaces\StatusCodes;
use PHPUnit\Framework\TestCase;
use Veniva\Lbs\Adapters\Interfaces\ITranslator;
use Doctrine\ORM\EntityManager;

class LanguageListTest extends TestCase
{
    protected $emStb;
    protected $transStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->emStb->method('getRepository')->willReturn(new LlStb());
        $this->transStb = $this->createMock(ITranslator::class);
    }

    public function testInvalidArg()
    {
        $langList = new LanguageList($this->transStb, $this->emStb);

        $this->expectException('TypeError');
        $langList->getList();//skip the required argument
    }

    public function testListSuccess()
    {
        $langList = new LanguageList($this->transStb, $this->emStb);
        $result = $langList->getList(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->has('title'));
        $this->assertTrue(is_object($result->get('langs_paginated')));
        $this->assertTrue(is_array($result->get('lang_data')));
    }
}

class LlStb
{
    function getLanguagesPaginated(){ return $this; }
    function setCurrentPageNumber(){}
}