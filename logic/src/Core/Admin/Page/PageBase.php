<?php

namespace Logic\Core\Admin\Page;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Model\Entity\ListingImage;
use Logic\Core\Result;
use Logic\Core\Services\CategoryTree;
use Logic\Core\Services\Language;
use Logic\Core\Admin\Form\Page as PageForm;
use Logic\Core\Stdlib\FileSystem;
use Logic\Core\Stdlib\Strings;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

abstract class PageBase extends BaseLogic
{
    /** @var EntityManager */
    protected $em;
    /** @var Language|null */
    protected $language;
    /** @var PageHelpers */
    protected $helpers;
    /** @var CategoryTree */
    protected $ct;
    /** @var PageForm */
    protected $form;
    /** @var SymfonyFilesystem */
    protected $fileSystem;
    /** @var FileSystem */
    protected $libFileSystem;

    public function __construct(ITranslator $translator, EntityManager $em, CategoryTree $ct, Language $language = null)
    {
        parent::__construct($translator);

        $this->language = $language;
        $this->em = $em;
        $this->ct = $ct;
        
        $this->helpers = new PageHelpers($language);    
        $this->form = new PageForm();
        $this->fileSystem = new SymfonyFilesystem();
        $this->libFileSystem = new FileSystem();
    }
    
    public function setAlias(array $data)
    {
        if(!empty($data['content'])){
            foreach($data['content'] as &$content){
                if(empty($content['alias'])){
                    $content['alias'] = Strings::alias($content['title']);
                }else{
                    $content['alias'] = Strings::alias($content['alias']);
                }
            }
        }
        
        return $data;
    }
    
    public function prepareForm(Listing $page, array &$data, &$hasImage = false): Result
    {
        $hasImage = (!empty($data['listing_image']['base64']) && !empty($data['listing_image']['name']));
        $data = $this->setAlias($data);

        $this->helpers->addEmptyContent($page);

        $this->form->bind($page);
        $this->form->get('category')->setValueOptions($this->ct->getSelectOptions());
        
        //validate image
        if($hasImage){
            $messages = [];
            $result = $this->form->validateBase64Image($data['listing_image']['name'], $data['listing_image']['base64'], $messages);
            if(!$result){
                $errorMessages = implode('<br />', $messages);//v_todo - translate
                return $this->result(StatusCodes::ERR_INVALID_FORM, $errorMessages, [
                    'form' => $this->form,
                    'page' => $page
                ]);
            }
        }

        $this->form->setData($data);
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form
        ]);
    }
    
    public function persistPage(PageForm $form, Listing $page): Listing
    {
        $category = $this->em->find(Category::class, ['id' => $form->getInputFilter()->getValue('category')]);
        $page->setOnlyCategory($category);

        $this->em->persist($page);
        
        return $page;
    }
    
    public function uploadImage(Listing $page, string $imgDir, array $data)
    {
        $uploadDir = $imgDir.$page->getId();
        $fileName = $uploadDir.'/'.$data['listing_image']['name'];
        $fileContent = base64_decode($data['listing_image']['base64']);
        $this->fileSystem->dumpFile($fileName, $fileContent);
    }
    
    public function addPageImage(Listing $page, array $data = null)
    {
        $listingImage = new ListingImage($page);
        if(!empty($data['listing_image']['name']))
            $listingImage->setImageName($data['listing_image']['name']);
    }

    /**
     * @return Language|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language|null $language
     * @return PageUpdate
     */
    public function setLanguage(Language $language = null)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return PageHelpers
     */
    public function getHelpers()
    {
        return $this->helpers;
    }

    /**
     * @param PageHelpers $helpers
     * @return PageUpdate
     */
    public function setHelpers($helpers)
    {
        $this->helpers = $helpers;
        return $this;
    }

    /**
     * @return PageForm
     */
    public function getForm(): PageForm
    {
        return $this->form;
    }

    /**
     * @param PageForm $form
     * @return PageUpdate
     */
    public function setForm(PageForm $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return SymfonyFilesystem
     */
    public function getFileSystem()
    {
        return $this->fileSystem;
    }

    /**
     * @param SymfonyFilesystem $fileSystem
     * @return PageUpdate
     */
    public function setFileSystem($fileSystem)
    {
        $this->fileSystem = $fileSystem;
        return $this;
    }
}