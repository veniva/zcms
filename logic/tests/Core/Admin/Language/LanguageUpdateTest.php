<?php

namespace Tests\Core\Admin\Language;

use Logic\Core\Admin\Language\LanguageUpdate;
use Logic\Core\Admin\Services\FlagCodes;
use Logic\Core\Form\Language as Form;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;
use Tests\Core\Admin\AdminBase;

class LanguageUpdateTest extends AdminBase
{
    protected $logic;
    protected $formStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->formStb = $this->createMock(Form::class);
        $this->logic = new LanguageUpdate($this->transStb, $this->emStb, new FlagCodes(), $this->formStb);
    }

    public function testShowTypeError()
    {
        $this->expectException('TypeError');
        $this->logic->showForm();
    }

    public function testInvalidId()
    {
        $result = $this->logic->checkIdIsValid(1);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testInvalidId2()
    {
        $result = $this->logic->checkIdIsValid(0);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testShowSuccess()
    {
        $this->emStb->method('find')->willReturn(true);

        $result = $this->logic->showForm(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }
    public function testUpdateTypeException()
    {
        $this->expectException('TypeError');
        $this->logic->update(1);
    }
    
    public function testUpdateInvalidForm()
    {
        $this->emStb->method('find')->willReturn(new Lang());
            
        $result = $this->logic->update(1, []);
        
        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertTrue($result->get('form') instanceof Form);
        $this->assertTrue($result->get('language') instanceof Lang);
    }

    public function testUpdateSuccess()
    {
        $this->emStb->method('find')->willReturn(new Lang());
        $this->formStb->method('isValid')->willReturn(true);

        $result = $this->logic->update(1, []);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->message !== null);
    }
}

class luStb
{
    function bind(){}
    function getDefaultLanguage(){}
    function get(){}
}