<?php

namespace Tests\Core\Admin\Category;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Category\CategoryCreate;
use Logic\Core\Admin\Category\CategoryHelpers;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Lang;
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
    protected $formStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->trStb = $this->createMock(ITranslator::class);
        $this->ctgTreeStb = $this->createMock(CategoryTree::class);
        $this->helpersStb = $this->createMock(CategoryHelpers::class);
        $this->formStb = $this->createMock(CategoryForm::class);

        $this->categoryCreate = new CategoryCreate($this->emStb, $this->trStb, $this->ctgTreeStb);
        $this->categoryCreate->setCategoryForm($this->formStb);
    }

    public function testShowInvalidParentId()
    {
        $result = $this->categoryCreate->showForm(-1);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
        $this->assertTrue(is_string($result->message));
    }

    public function testShowNoLanguages()
    {

        $this->emStb->method('getRepository')->willReturn(new CatCreateStb);
        $result = $this->categoryCreate->showForm(1);

        $this->assertEquals(CategoryCreate::ERR_NO_LANG, $result->status);
        $this->assertTrue(is_string($result->message));
    }

    public function testShowSuccess()
    {
        $stb = $this->createMock(CatCreateStb::class);
        $stb->method('countLanguages')->willReturn(1);

        $this->emStb->method('getRepository')->willReturn($stb);
        $this->helpersStb->method('prepareFormWithLanguage')->willReturn(new CategoryForm());
        $this->categoryCreate->setHelpers($this->helpersStb);

        $result = $this->categoryCreate->showForm(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue($result->form instanceof CategoryForm);
        $this->assertTrue($result->category instanceof Category);
    }

    public function testCategoryCreateInvalidParam()
    {
        $result = $this->categoryCreate->create([]);
        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);

        $result = $this->categoryCreate->create(['parent']);
        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);

        $result = $this->categoryCreate->create(['content']);
        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result->status);
    }

    public function testCategoryCreateNoLanguage()
    {
        $this->emStb->method('getRepository')->willReturn(new CatCreateStb);
        $result = $this->categoryCreate->create([
            'parent_id' => true,
            'content' => true
        ]);

        $this->assertEquals(CategoryCreate::ERR_NO_LANG, $result->status);
        $this->assertTrue(is_string($result->message));
    }

    public function testCategoryCreateInvalidForm()
    {
        $this->prepare();
        $this->formStb->method('isFormValid')->willReturn(false);

        $result = $this->categoryCreate->create([
            'parent_id' => true,
            'content' => []
        ]);

        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result->status);
        $this->assertTrue($result->has('category'));
        $this->assertTrue($result->has('form'));
    }

    public function testCategoryCreateSuccess()
    {
        $this->prepare();
        $this->formStb->method('isFormValid')->willReturn(true);

        $result = $this->categoryCreate->create([
            'parent_id' => true,
            'content' => []
        ]);
        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(is_string($result->message));
    }

    protected function prepare()
    {
        $langRepoStb = $this->createMock(CatCreateStb::class);
        $langRepoStb->method('countLanguages')->willReturn(1);
        $categStb = $this->createMock(CategoryRepository::class);

        $valueMap = [
            [Lang::class, $langRepoStb],
            [Category::class, $categStb]
        ];

        $this->emStb->expects($this->any())->method('getRepository')->will($this->returnValueMap($valueMap));

        $helpersStb = $this->createMock(CategoryHelpers::class);
        $helpersStb->method('prepareFormWithLanguage')->willReturn($this->formStb);
        $this->categoryCreate->setHelpers($helpersStb);
    }
}

class CatCreateStb
{
    function countLanguages(){}
}