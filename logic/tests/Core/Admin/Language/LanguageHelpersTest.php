<?php

namespace Logic\Tests\Core\Admin\Language;

use Doctrine\ORM\EntityRepository;
use Logic\Core\Admin\Language\LanguageHelpers;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Tests\Core\Admin\AdminBase;

class LanguageHelpersTest extends AdminBase
{
    protected $helpers;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->helpers = new LanguageHelpers($this->emStb);
    }

    public function testNoDefaultLanguageError()
    {
        $repoStb = $this->createMock(EntityRepository::class);
        $this->emStb->method('getRepository')->willReturn($repoStb);

        $result = $this->helpers->getDefaultLanguage();

        $this->assertEquals(LanguageHelpers::ERR_NO_DEFAULT_LANGUAGE, $result->status);
    }

    public function testGetDefaultLanguageSuccess()
    {
        $repoStb = $this->createMock(EntityRepository::class);
        $repoStb->method('findOneBy')->willReturn(new \stdClass());
        $this->emStb->method('getRepository')->willReturn($repoStb);

        $result = $this->helpers->getDefaultLanguage();

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->get('default_language') instanceof \stdClass);
    }
}