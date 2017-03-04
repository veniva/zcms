<?php

namespace Tests\Admin\Category;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Category\CategoryCreate;
use Logic\Core\Admin\Category\CategoryHelpers;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\LangRepository;
use Logic\Core\Services\CategoryTree;
use PHPUnit\Framework\TestCase;
use Logic\Core\Form\Category as CategoryForm;

class CategoryCreateTest extends TestCase
{

    protected $emStb;
    protected $trStb;
    protected $ctgTreeStb;
    protected $categoryCreate;
    protected $helpersStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->trStb = $this->createMock(ITranslator::class);
        $this->ctgTreeStb = $this->createMock(CategoryTree::class);
        $this->helpersStb = $this->createMock(CategoryHelpers::class);

        $this->categoryCreate = new CategoryCreate($this->emStb, $this->trStb, $this->ctgTreeStb);
    }

    public function testShowInvalidParentId()
    {
        $result = $this->categoryCreate->showForm(-1);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result['status']);
        $this->assertArrayHasKey('message', $result);
    }

    public function testShowNoLanguages()
    {

        $this->emStb->method('getRepository')->willReturn(new CatCreateStb);
        $result = $this->categoryCreate->showForm(1);

        $this->assertEquals(CategoryCreate::ERR_NO_LANG, $result['status']);
        $this->assertArrayHasKey('message', $result);
    }

    public function testShowSuccess()
    {
        $stb = $this->createMock(CatCreateStb::class);
        $stb->method('countLanguages')->willReturn(1);

        $this->emStb->method('getRepository')->willReturn($stb);
        $this->helpersStb->method('prepareFormWithLanguage')->willReturn(new CategoryForm());
        $this->categoryCreate->setHelpers($this->helpersStb);

        $result = $this->categoryCreate->showForm(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
        $this->assertArrayHasKey('form', $result);
        $this->assertArrayHasKey('category', $result);
        $this->assertTrue($result['form'] instanceof CategoryForm);
        $this->assertTrue($result['category'] instanceof Category);
    }
}

class CatCreateStb
{
    function countLanguages(){}
}