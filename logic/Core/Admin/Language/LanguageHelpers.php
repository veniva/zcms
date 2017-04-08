<?php

namespace Logic\Core\Admin\Language;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Result;

class LanguageHelpers extends BaseLogic
{
    const ERR_NO_DEFAULT_LANGUAGE = 'cl.no-default-language';
    
    /** @var EntityManager */
    protected $em;
    
    public function __construct(EntityManager $em, ITranslator $translator = null)
    {
        parent::__construct($translator);
        $this->em = $em;
    }

    /**
     * Persist only: copy the page & category content of the default language to the new language
     * Needs EntityManager::flush() afterwords
     * 
     * @param Lang $language The new language being created
     * @param int $defaultLanguageId The ID of the language that is default for the application at the moment
     * @return void
     */
    public function fillDefaultContent(Lang $language, int $defaultLanguageId)
    {
        /*
         * 1. get the default language content for categories & pages
         * 2. create a new content entities of the given language for categories & pages
         * 3. add the new content entities of the given language to the categories & pages
         */
        
        //parse through all the categories
        $categories = $this->em->getRepository(Category::class)->findAll();
        foreach($categories as $category) {
            $defaultCategoryContent = $category->getSingleCategoryContent($defaultLanguageId);
            //copy category's content of the default language as a new language category content
            if($defaultCategoryContent) {
                $newContent = clone $defaultCategoryContent;
                $newContent->setLang($language);
                $category->addCategoryContent($newContent);
                $this->em->persist($category);
            }

            //copy page's content of the default language as a new language page content
            foreach($category->getListings() as $page) {
                $defaultListingContent = $page->getSingleListingContent($defaultLanguageId);
                if($defaultListingContent){
                    $newListingContent = clone $defaultListingContent;
                    $newListingContent->setLang($language);
                    $page->addContent($newListingContent);
                    $this->em->persist($page);
                }
            }
        }
    }

    /**
     * Get the currently default language for the application
     * @return Result
     */
    public function getDefaultLanguage()
    {
        $defaultLanguage = $this->em->getRepository(Lang::class)->findOneBy(['status' => Lang::STATUS_DEFAULT]);
        if(!$defaultLanguage){
            return $this->result(self::ERR_NO_DEFAULT_LANGUAGE, 'No default language found in the database. Please contact your DB administrator to fix the issue');
        }
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'default_language' => $defaultLanguage
        ]);
    }
}