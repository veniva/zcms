<?php

namespace Logic\Core\Admin\Category;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\CategoryRepository;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Result;
use Symfony\Component\Filesystem\Filesystem;

class CategoryDelete extends BaseLogic
{
    const ERR_CATEG_NOT_FOUND = 'd_cat.no-categ';
    
    /** @var EntityManager */
    protected $em;
    
    /** @var ITranslator */
    protected $translator;

    /** @var Filesystem */
    protected $filesystem;
    
    public function __construct(EntityManager $em, ITranslator $translator, Filesystem $fileSystem = null)
    {
        parent::__construct($translator);
        
        $this->em = $em;
        $this->translator = $translator;
        $this->filesystem = $fileSystem ? $fileSystem : new Filesystem();
    }
    
    public function delete(int $id, string $imgDir):Result
    {
        if($id < 1){
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        /** @var Category | null $category */
        $category = $this->em->find(Category::class, $id);
        if(!$category){
            return $this->result(self::ERR_CATEG_NOT_FOUND, 'Category not found');
        }
        
        $this->deleteListingImages($category, $imgDir);
        
        //V_TODO - delete all the children in table categories
        
        $this->em->remove($category);
        $this->em->flush();
        
        $successMessage = 'The category and all the listings in it were removed successfully';
        return $this->result(StatusCodes::SUCCESS, $successMessage, [
            'parent' => (int)$category->getParent()
        ]);
    }

    protected function deleteListingImages(Category $category, string $path)
    {
        /** @var CategoryRepository $categRepo */
        $categRepo = $this->em->getRepository(Category::class);

        //dept-first recursion
        foreach($categRepo->getChildren($category) as $subCategory){
            $this->deleteListingImages($subCategory, $path);
        }

        foreach($category->getListings() as $listing){
            $this->filesystem->remove($path.$listing->getId());
        }
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param Filesystem $filesystem
     * @return CategoryDelete
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }
}