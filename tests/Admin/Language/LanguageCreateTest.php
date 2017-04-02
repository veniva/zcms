<?php

namespace Tests\Admin\Language;


use Logic\Core\Admin\Language\LanguageCreate;
use Logic\Core\Admin\Services\FlagCodes;
use Logic\Core\Form\Language as Form;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Lang;
use Tests\Admin\AdminBase;

class LanguageCreateTest extends AdminBase
{
    protected $logic;
    protected $formStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->formStb = $this->createMock(Form::class);
        $this->logic = new LanguageCreate($this->transStb, $this->emStb, new FlagCodes(), $this->formStb);
    }
    
    public function testShowSuccess()
    {
        $result = $this->logic->showForm();

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->has('form'));
        $this->assertTrue($result->get('language') instanceof Lang);
    }
}