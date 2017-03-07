<?php

namespace Tests\Admin\Category;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Category\CategoryDelete;
use Logic\Core\Interfaces\StatusCodes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class CategoryDeleteTest extends TestCase
{
    protected $emStb;
    protected  $transStb;
    /** @var CategoryDelete */
    protected $categoryDelete;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->emStb = $this->createMock(EntityManager::class);
        $this->transStb = $this->createMock(ITranslator::class);
        $this->categoryDelete = new CategoryDelete($this->emStb, $this->transStb);
    }

    public function testInvalidId()
    {
        $result = $this->categoryDelete->delete(0, '');

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $result['status']);
        $this->assertArrayHasKey('message', $result);
    }
    
    public function testDeleteCategNotFound()
    {
        $result = $this->categoryDelete->delete(1, '');

        $this->assertEquals(CategoryDelete::ERR_CATEG_NOT_FOUND, $result['status']);
        $this->assertArrayHasKey('message', $result);
    }

    public function deleteSuccess()
    {
        $fileSystemStb = $this->createMock(Filesystem::class);
        $this->categoryDelete->setFilesystem($fileSystemStb);
        $result = $this->categoryDelete->delete(1, '');

        $this->assertEquals(StatusCodes::SUCCESS, $result['status']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('parent', $result);
    }
}