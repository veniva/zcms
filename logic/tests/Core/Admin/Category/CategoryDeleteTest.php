<?php

namespace Tests\Core\Admin\Category;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Category\CategoryDelete;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
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
        $response = $this->categoryDelete->delete(0, '');

        $this->assertEquals(StatusCodes::ERR_INVALID_PARAM, $response->status);
        $this->assertTrue(is_string($response->message));
    }
    
    public function testDeleteCategNotFound()
    {
        $response = $this->categoryDelete->delete(1, '');

        $this->assertEquals(CategoryDelete::ERR_CATEG_NOT_FOUND, $response->status);
        $this->assertTrue(is_string($response->message));
    }

    public function testDeleteSuccess()
    {
        $fileSystemStb = $this->createMock(Filesystem::class);
        $this->categoryDelete->setFilesystem($fileSystemStb);
        $this->emStb->method('find')->willReturn(new Category());
        $this->emStb->method('getRepository')->willReturn(new CTStb());
        $result = $this->categoryDelete->delete(1, '');

        $this->assertEquals(StatusCodes::SUCCESS, $result->status);
        $this->assertTrue(is_string($result->message));
        $this->assertTrue($result->has('parent'));
    }
}

class CTStb{
    function getChildren(){return [];}
}