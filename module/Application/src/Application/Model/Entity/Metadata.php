<?php

namespace Application\Model\Entity;

/**
 * @Entity @Table(name="metadata")
 */
class Metadata
{
    /**
     * @Id @ManyToOne(targetEntity="Listing", inversedBy="content")
     */
    protected $listing;

    /**
     * @Id @ManyToOne(targetEntity="Lang")
     */
    protected $lang;

    /**
     * @Column(type="string")
     */
    protected $title;

    /**
     * @Column(type="string")
     */
    protected $description;

    /**
     * @Column(type="string")
     */
    protected $keywords;

    public function __construct($listingId, $langId)
    {
        $this->listing = $listingId;
        $this->lang = $langId;
    }

    /**
     * @return mixed
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param mixed $listing
     */
    public function setListing($listing)
    {
        $this->listing = $listing;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
}
