<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace ApplicationTest\Controller;

use ApplicationTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CategoryControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setApplicationConfig(
            $this->serviceManager->get('ApplicationConfig')
        );
        parent::setUp();
    }

    public function testBreadcrumb()
    {
        $this->dispatch('/');
        $renderer = $this->serviceManager->get('ViewRenderer');
        $this->assertEquals('Top', trim($renderer->breadcrumb()));
    }
}