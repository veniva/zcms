<?php

namespace Tests;


use Doctrine\ORM\EntityManager;
use Logic\Core\Category;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Services\Language;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    protected $emStub;
    protected $langServiceStub;
    protected $currentLangStub;
    protected $repoStub;
    protected $categEntityStub;
    
    public function __construct()
    {
        $this->emStub = $this->createMock(EntityManager::class);
        $this->repoStub = $this->createMock(CategoryRepository::class);
        $this->categEntityStub = $this->createMock(CategoryEntity::class);
        $this->langServiceStub = $this->createMock(Language::class);
        $this->currentLangStub = $this->createMock(Lang::class);

        //configure stubs
        $this->langServiceStub->method('getCurrentLanguage')->willReturn($this->currentLangStub);
        $this->currentLangStub->method('getId')->willReturn(1);
        $this->emStub->method('getRepository')->willReturn($this->repoStub);
        $this->categEntityStub->method('getSingleCategoryContent')->willReturn('blah');
    }

    public function testNoAlias()
    {
        $logic = new Category($this->emStub, $this->langServiceStub);
        $result = $logic->process('');
        $this->assertEquals(Category::ERR_NO_ALIAS, $result['status']);
    }
    
    public function testNoCategory()
    {
        $this->repoStub->method('getCategoryByAliasAndLang')->willReturn(null);
        $logic = new Category($this->emStub, $this->langServiceStub);
        $result = $logic->process('some');
        $this->assertEquals(Category::ERR_NO_CATEGORY, $result['status']);
    }
    
    public function testSuccess()
    {
        $this->repoStub->method('getCategoryByAliasAndLang')->willReturn($this->categEntityStub);
        $this->repoStub->method('__call')->with('findByParent')->willReturn(true);
        $logic = new Category($this->emStub, $this->langServiceStub);
        $result = $logic->process('some');
        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
    }
}