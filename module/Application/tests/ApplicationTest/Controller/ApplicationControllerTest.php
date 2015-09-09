<?php

namespace ApplicationTest\Controller;


use Application\Service\Invokable\Layout;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ApplicationControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        parent::setUp();
    }

    public function testLanguages()
    {
        $this->dispatch('/');
        $langs = Layout::getAllLangs();

        //test if it is array or Traversable
        if(!$langs instanceof \Traversable && !is_array($langs)){
            throw new \PHPUnit_Framework_AssertionFailedError('$langs is not of type array object');
        }
    }

    public function testCategories()
    {
        $this->dispatch('/');
        $categories = Layout::getTopCategories();
        $this->assertInternalType('array', $categories);

        foreach($categories as $category){
            $this->assertInternalType('array', $category['listings']);
        }
    }

    public function testBreadcrumb()
    {
        $this->dispatch('/');
        $this->assertEmpty(Layout::breadcrumb());

        $this->dispatch('/category');
        $this->assertInternalType('array', Layout::breadcrumb());
    }
}
