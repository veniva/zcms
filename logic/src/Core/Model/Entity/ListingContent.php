<?php

namespace Logic\Core\Model\Entity;

/**
 * @Entity @Table(name="listings_content")
 */
class ListingContent
{
    /**
     * @Id @ManyToOne(targetEntity="Listing", inversedBy="content")
     */
    protected $listing;

    /**
     * @Id @ManyToOne(targetEntity="Lang", inversedBy="listingContent")
     * @OrderBy({"status" = "DESC"})
     */
    protected $lang;

    /**
     * @Column(type="string")
     */
    protected $alias;

    /**
     * @Column(type="string")
     */
    protected $link;

    /**
     * @Column(type="string")
     */
    protected $title;

    /**
     * @Column(type="text")
     */
    protected $text;

    /**
     * @Column(type="string", name="meta_title", length=255, nullable=true)
     */
    protected $metaTitle = null;

    /**
     * @Column(type="text", name="meta_description", length=255, nullable=true)
     */
    protected $metaDescription = null;

    /**
     * @Column(type="string", name="meta_keywords", nullable=true)
     */
    protected $metaKeywords = null;

    public function __construct(Listing $listing = null, Lang $lang = null)
    {
        if($listing)
            $this->setListing($listing);
        if($lang)
            $this->setLang($lang);
    }

    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param mixed $listing
     */
    public function setListing(Listing $listing)
    {
        $listing->addContent($this);
        $this->listing = $listing;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param mixed $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return Lang
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param Lang $lang
     */
    public function setLang(Lang $lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return mixed
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return mixed
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return mixed
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }
}
