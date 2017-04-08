<?php

namespace Tests\Admin\Language;

use Doctrine\ORM\EntityRepository;
use Logic\Core\Admin\Language\LanguageCreate;
use Logic\Core\Admin\Services\FlagCodes;
use Logic\Core\Form\Language as Form;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Result;
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

    public function testCreateInvalidForm()
    {
        $result = $this->logic->create([]);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_FORM_MSG, $result->message);
        $this->assertTrue($result->get('form') instanceof Form);
        $this->assertTrue($result->get('language') instanceof Lang);
    }
    
    public function testCreateSuccess()
    {
        $lcStb = new lcStb();
        $stb = $this->createMock(lcStb::class);

        $repoStb = $this->createMock(EntityRepository::class);

        $resultStb = $this->createMock(Result::class);
        $resultStb->status = StatusCodes::SUCCESS;
        $resultStb->method('get')->willReturn($lcStb);

        $stb->method('getDefaultLanguage')->willReturn($resultStb);

        $this->emStb->method('getRepository')->willReturn($repoStb);
        $this->formStb->method('isValid')->willReturn(true);

        $this->logic->setHelpers($stb);

        $result = $this->logic->create([]);
        
        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue((bool)strlen($result->message));
    }
}

class lcStb
{
    var $status = StatusCodes::SUCCESS;

    function fillDefaultContent(){}
    function getDefaultLanguage(){ return new self; }
    function getId(){ return 1; }
}