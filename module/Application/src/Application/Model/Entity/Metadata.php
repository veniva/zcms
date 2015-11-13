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
     * @Id @ManyToOne(targetEntity="Lang", inversedBy="metadata")
     */
    protected $lang;

    /**
     * @Column(type="string", name="meta_title", nullable=true)
     */
    protected $metaTitle = null;

    /**
     * @Column(type="string", name="meta_description")
     */
    protected $metaDescription = null;//v_todo - annotate all the ORM with the full set of options: http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#annref-column

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
    public function setListing(Listing $listing)
    {
        $listing->addMetadata($this);
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
