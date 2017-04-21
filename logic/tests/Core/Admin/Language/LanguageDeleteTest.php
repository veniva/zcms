<?php

namespace Logic\Tests\Core\Admin\Language;

use Logic\Core\Admin\Language\LanguageDelete;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;
use Logic\Tests\Core\Admin\AdminBase;

class LanguageDeleteTest extends AdminBase
{
    protected $logic;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->logic = new LanguageDelete($this->transStb, $this->emStb);
    }

    public function testTypeError()
    {
        $this->expectException('TypeError');
        $this->logic->delete('s');
    }

    public function testInvalidParam()
    {
        $result = $this->logic->delete(1);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertEquals(StatusMessages::ERR_INVALID_PARAM_MSG, $result->message);
    }

    public function testCannotDeleteDefault()
    {
        $language = new Lang();
        $language->setStatus(Lang::STATUS_DEFAULT);
        $this->emStb->method('find')->willReturn($language);

        $result = $this->logic->delete(1);

        $this->assertEquals(LanguageDelete::ERR_CANNOT_DELETE_DEFAULT, $result->status);
        $this->assertEquals(LanguageDelete::ERR_CANNOT_DELETE_DEFAULT_MSG, $result->message);
    }

    public function testDeleteSuccess()
    {
        $language = new Lang();
        $this->emStb->method('find')->willReturn($language);

        $result = $this->logic->delete(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
    }
}