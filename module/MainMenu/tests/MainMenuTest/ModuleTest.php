<?php
namespace MainMenuTest;

class ModuleTest extends \PHPUnit_Framework_TestCase{

    public function testCategories()
    {
        $module = new \MainMenu\Module();
        $categories = $module->getTopCategories();
        $this->assertInternalType('array', $categories);

        foreach($categories as $category){
            $this->assertInternalType('array', $category['listings']);
        }
    }
}