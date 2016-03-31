<?php
namespace MainMenuTest;

use ApplicationTest\Bootstrap;

class ModuleTest extends \PHPUnit_Framework_TestCase{

    public function testCategories()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $module = new \MainMenu\Module();
        $categories = $module->getTopCategories($serviceManager);
        $this->assertInternalType('array', $categories);

        foreach($categories as $category){
            $this->assertInternalType('array', $category['listings']);
        }
    }
}