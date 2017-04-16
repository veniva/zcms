<?php

namespace Tests\Core\Admin\Page;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Services\CategoryTree;
use Logic\Core\Services\Language;

class PageBase extends TestCase
{
    protected $transStb;
    protected $emStb;
    protected $ctStb;
    protected $lngStb;
    protected $pbStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->transStb = $this->createMock(ITranslator::class);
        $this->transStb->method('translate')->willReturnArgument(0);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->ctStb = $this->createMock(CategoryTree::class);
        $this->lngStb = $this->createMock(Language::class);
        $this->pbStb = $this->createMock(PbStb::class);
        $this->emStb->method('getRepository')->willReturn($this->pbStb);


    }
}

class PageBaseExt extends \Logic\Core\Admin\Page\PageBase{}

class PbStb
{
    function countAll(){}
}