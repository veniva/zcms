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
