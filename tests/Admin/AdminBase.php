<?php

namespace Tests\Admin;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;

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