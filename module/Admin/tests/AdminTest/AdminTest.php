<?php

namespace AdminTest;

use Application\Service\Invokable\Misc;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AdminTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );
        parent::setUp();
    }

    public function testCategoryTree()
    {
        $categoryTree = new \Admin\CategoryTree\CategoryTree($this->getApplicationServiceLocator());
        $categories = $categoryTree->getCategories();
        $this->assertInternalType('array', $categories);
    }

    public function testListingForm()
    {
        $sl = $this->getApplicationServiceLocator();
        $obj = new \Admin\Form\Listing($sl->get('listing-entity'), Misc::getActiveLangs());
        if(!$obj->getForm() instanceof \Zend\Form\Form){
            throw new \PHPUnit_Framework_AssertionFailedError('A service object is not an instance of '.'\Zend\Form\Form');
        }else{
            $this->assertTrue(true);
        }
    }
}