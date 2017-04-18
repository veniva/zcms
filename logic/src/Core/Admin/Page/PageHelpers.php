<?php

namespace Logic\Core\Admin\Page;

use Logic\Core\Model\Entity\Listing;
use Logic\Core\Model\Entity\ListingContent;
use Logic\Core\Services\Language;

class PageHelpers
{
    /** @var Language */
    protected $language;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function addEmptyContent(Listing $page)
    {
        $contentLangIDs = [];
        foreach($page->getContent() as $content){
            $contentLangIDs[] = $content->getLang()->getId();
        }
        if($this->language && $this->language->getActiveLanguages()){
            foreach($this->language->getActiveLanguages() as $language){
                if(!in_array($language->getId(), $contentLangIDs)){
                    new ListingContent($page, $language);
                }
            }
        }
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     * @return PageHelpers
     */
    public function setLanguage(Language $language = null)
    {
        $this->language = $language;
        return $this;
    }

}