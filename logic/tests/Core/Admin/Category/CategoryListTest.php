<?php
namespace Tests\Core\Admin\Category;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Category\CategoryList;
use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Services\Language;
use PHPUnit\Framework\TestCase;

class CategoryListTest extends TestCase
{
    protected $trStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->trStb = $this->createMock(ITranslator::class);

        parent::__construct($name, $data, $dataName);
    }

    public function testGetList()
    {
        $emStb = $this->createMock(EntityManager::class);
        $lngStb = $this->createMock(Language::class);
        $categRepStb = $this->createMock(CategoryRepository::class);
        $langEntStb = $this->createMock(Lang::class);
        $stbCT = new StbCT();


        $emStb->method('getRepository')->willReturn($categRepStb);
        $categRepStb->method('getPaginatedCategories')->willReturn($stbCT);
        $lngStb->method('getDefaultLanguage')->willReturn($langEntStb);
        $langEntStb->method('getId')->willReturn(1);

        $categoryLogic = new CategoryList();
        $result = $categoryLogic->getList($emStb, $lngStb, 1, 1);

        $this->assertTrue(is_array($result->categories));
        $this->assertTrue(is_object($result->categories_paginated));
    }
}

class StbCT
{
    function setCurrentPageNumber(){}
}