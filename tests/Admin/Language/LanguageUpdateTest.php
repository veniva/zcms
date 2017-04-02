<?php

namespace Tests\Admin\Language;


use Logic\Core\Admin\Language\LanguageUpdate;
use Logic\Core\Admin\Services\FlagCodes;
use Logic\Core\Form\Language;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Tests\Admin\AdminBase;

class LanguageUpdateTest extends AdminBase
{
    protected $logic;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStb->method('getRepository')->willReturn($this->createMock(luStb::class));
        $this->logic = new LanguageUpdate($this->transStb, $this->emStb, new FlagCodes());
    }

    public function testShowTypeError()
    {
        $this->expectException('TypeError');
        $this->logic->showForm();
    }

    public function testInvalidParam()
    {
        $result = $this->logic->showForm(1);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testInvalidParam2()
    {
        $result = $this->logic->showForm(0);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testShowSuccess()
    {
        $this->emStb->method('find')->willReturn(true);
        $formStb = $this->createMock(Language::class);
        $this->logic->setForm($formStb);

        $result = $this->logic->showForm(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }
}

class luStb
{
    function countLanguages(){}
}