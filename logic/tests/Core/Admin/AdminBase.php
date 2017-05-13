<?php

namespace Logic\Tests\Core\Admin;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Veniva\Lbs\Adapters\Interfaces\ITranslator;

class AdminBase extends TestCase
{
    protected $transStb;
    protected $emStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->transStb = $this->createMock(ITranslator::class);
        $this->transStb->method('translate')->willReturnArgument(0);

        $this->emStb = $this->createMock(EntityManager::class);
    }
}