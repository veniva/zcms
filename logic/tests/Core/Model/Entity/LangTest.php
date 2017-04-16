<?php

namespace Tests\Core\Model\Entity;

use Logic\Core\Model\Entity\Lang;
use PHPUnit\Framework\TestCase;

class LangTest extends TestCase
{
    public function testStatusDefaultCheck()
    {
        $this->assertTrue(Lang::isLanguageDefault(Lang::STATUS_DEFAULT));
        $this->assertFalse(Lang::isLanguageDefault(Lang::STATUS_ACTIVE));
    }
}