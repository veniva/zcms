<?php

namespace AdminTest;

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
}