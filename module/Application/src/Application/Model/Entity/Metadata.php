<?php

namespace Application\Model\Entity;

use Zend\Form\Annotation;

/**
 * @Annotation\Name("metadata")
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 *
 * @Entity @Table(name="metadata")
 */
class Metadata
{
    /**
     * @Annotation\Exclude()
     *
     * @Id @ManyToOne(targetEntity="Listing", inversedBy="content")
     */
    protected $listing;

    /**
     * @Annotation\Exclude()
     *
     * @Id @ManyToOne(targetEntity="Lang")
     */
    protected $lang;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Validator({"name": "StringLength", "options": {"max": 255}})
     * @Annotation\Options({"label": "Meta title"})
     *
     * @Column(type="string", name="meta_title")
     */
    protected $metaTitle;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Validator({"name": "StringLength", "options": {"max": 255}})
     * @Annotation\Options({"label": "Meta description"})
     *
     * @Column(type="string", name="meta_description")
     */
    protected $metaDescription;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Validator({"name": "StringLength", "options": {"max": 255}})
     * @Annotation\Options({"label": "Meta keywords"})
     *
     * @Column(type="string", name="meta_keywords")
     */
    protected $metaKeywords;

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
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param mixed $metaTitle
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
     * @param mixed $metaDescription
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
     * @param mixed $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }
}
