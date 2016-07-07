<?php

namespace ApplicationTest\Stdlib;

use ApplicationTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Stdlib;

class CalcTest extends AbstractHttpControllerTestCase
{
    protected $serviceManager;
    /** @var Stdlib\Calc  */
    protected $calc;
    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setApplicationConfig(
            $this->serviceManager->get('ApplicationConfig')
        );
        $this->calc = new Stdlib\Calc();
        parent::setUp();
    }
    
    public function testByteToKilobyte()
    {
        $this->assertEquals(1, $this->calc->bytesToKilobytes(1024));
    }

    public function testBytesToMegabytes()
    {
        $this->assertEquals(1, $this->calc->bytesToMegabytes(1048576));
    }

    public function testKilobytesToBytes()
    {
        $this->assertEquals(1024, $this->calc->kilobytesToBytes(1));
    }

    public function testKilobytesToMegabytes()
    {
        $this->assertEquals(1, $this->calc->kilobytesToMegabytes(1024));
    }

    public function testMegabytesToBytes()
    {
        $this->assertEquals(1048576, $this->calc->megabytesToBytes(1));
    }

    public function testMegabytesToKilobytes()
    {
        $this->assertEquals(1024, $this->calc->megabytesToKilobytes(1));
    }
}