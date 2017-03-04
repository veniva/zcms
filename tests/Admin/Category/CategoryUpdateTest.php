<?php
/**
 * User: ventsi
 * Date: 25.2.2017 г.
 * Time: 21:19 ч.
 */

namespace Tests\Admin\Category;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Category\CategoryUpdate;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Category;
use PHPUnit\Framework\TestCase;
use Logic\Core\Form\Category as CategoryForm;
use Logic\Core\Services\Language;
use Logic\Core\Services\CategoryTree;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Zend\Form\Element;

class CategoryUpdateTest extends TestCase
{
    protected $trStb;
    protected $langServiceStb;
    protected $categoryTreeStb;
    protected $emStb;
    protected $categoryUpdate;
    protected $formStb;
    protected $ctgUpdStb;
    protected $hlpStb;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->trStb = $this->createMock(ITranslator::class);
        $this->trStb->method('translate')->willReturnArgument(0);

        $this->langServiceStb = $this->createMock(Language::class);
        $this->langServiceStb->method('getActiveLanguages')->willReturn([]);

        $this->categoryTreeStb = $this->createMock(CategoryTree::class);
        $this->categoryTreeStb->method('getSelectOptions')->willReturn([]);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->formStb = $this->createMock(CategoryForm::class);

        $this->ctgUpdStb = $this->createMock(CategoryUpdate::class);
        $this->categoryUpdate = new CategoryUpdate($this->emStb, $this->trStb, $this->categoryTreeStb);
        $this->categoryUpdate->getHelpers()->setLanguageService($this->langServiceStb);

        parent::__construct($name, $data, $dataName);
    }

    public function testPrepareForm()
    {
        $form = $this->categoryUpdate->getHelpers()->prepareFormWithLanguage(new CategoryEntity(), 'Top');
        $parentElement = $form->get('parent');
        $emptyOption = $parentElement->getEmptyOption();

        $this->assertTrue($form instanceof CategoryForm);
        $this->assertTrue($emptyOption === 'Top');
    }

    //<editor-fold desc="Get method test">
    public function testGetCategError()
    {
        $this->emStb->method('find')->willReturn(null);
        $logic = $this->categoryUpdate;
        $result = $logic->get(1);

        $this->assertEquals(CategoryUpdate::ERR_CATEGORY_NOT_FOUND, $result['status']);
    }

    public function testGetIdError()
    {
        $logic = $this->categoryUpdate;
        $result = $logic->get(0);

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result['status']);
    }

    public function testGetSuccess()
    {
        $this->emStb->method('find')->willReturn(new CategoryEntity());
        $logic = $this->categoryUpdate;
        $result = $logic->get(1);

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
    }
    //</editor-fold>
    
    public function testUpdateCategError()
    {
        $this->emStb->method('find')->willReturn(null);
        $result = $this->categoryUpdate->update(1, []);

        $this->assertEquals(CategoryUpdate::ERR_CATEGORY_NOT_FOUND, $result['status']);
        $this->assertArrayHasKey('message', $result);
    }
    
    public function testUpdateInvalidForm()
    {
        $this->prepare();

        $this->formStb->method('isFormValid')->willReturn(false);

        $result = $this->categoryUpdate->update(1, [
            'parent' => true,
            'content' => []
        ]);
        $this->assertEquals(StatusCodes::ERR_INVALID_FORM, $result['status']);
        $this->assertArrayHasKey('form', $result);
        $this->assertArrayHasKey('category', $result);
    }

    public function testUpdateSuccess()
    {
        $this->prepare();
        $this->formStb->method('isFormValid')->willReturn(true);
        $elemStb = $this->createMock(Element\Select::class);
        $this->formStb->method('get')->willReturn($elemStb);
        $this->categoryUpdate->setCategoryForm($this->formStb);
        $result = $this->categoryUpdate->update(1, [
            'parent' => true,
            'content' => []
        ]);

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('parent', $result);
    }

    private function prepare()
    {
        $categStb = $this->createMock(Category::class);
        $categStb->method('getContent')->willReturn([]);

        $ctgRepoStb = $this->createMock(CategoryRepository::class);
        $ctgRepoStb->method('getChildren')->willReturn([]);

        $this->emStb->method('find')->willReturn($categStb);
        $this->emStb->method('getRepository')->willReturn($ctgRepoStb);
    }
}

class CtgUStb
{
    function getChildren(){}
    function findOneById(){}
}