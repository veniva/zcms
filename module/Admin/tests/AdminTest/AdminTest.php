<?php

namespace AdminTest;

use Admin\Validator\Password;
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
        $form = new \Admin\Form\Listing($sl->get('entity-manager'), null);
        if(!$form instanceof \Zend\Form\Form){
            throw new \PHPUnit_Framework_AssertionFailedError('A service object is not an instance of '.'\Zend\Form\Form');
        }else{
            $this->assertTrue(true);
        }
    }

    public function testValidatorPassword()
    {
        $validator = new Password();
        $result = $validator->isValid('some-value');
        $this->assertInternalType('bool', $result);
    }
}