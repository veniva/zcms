<?php

namespace Application\Model\Entity;

use Zend\Form\Annotation;

/**
 * @Annotation\Name("listing")
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 *
 * @Entity @Table(name="listings_content")
 */
class ListingContent
{
    /**
     * @Annotation\Exclude()
     *
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Annotation\Exclude()
     *
     * @ManyToOne(targetEntity="Listing", inversedBy="content")
     */
    protected $listing;

    /**
     * @Annotation\Exclude()
     *
     * @ManyToOne(targetEntity="Lang", inversedBy="listingContent")
     */
    protected $lang;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Validator({"name": "StringLength", "options": {"max": 255}})
     * @Annotation\Options({"label": "Alias"})
     *
     * @Column(type="string")
     */
    protected $alias;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Validator({"name": "StringLength", "options": {"max": 15}})
     * @Annotation\Options({"label": "Link"})
     *
     * @Column(type="string")
     */
    protected $link;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Validator({"name": "StringLength", "options": {"max": 15}})
     * @Annotation\Options({"label": "Title"})
     *
     * @Column(type="string")
     */
    protected $title;

    /**
     * @Annotation\Type("textarea")
     * @Annotation\Validator({"name": "NotEmpty"})
     * @Annotation\Options({"label": "Content"})
     *
     * @Column(type="string")
     */
    protected $text;

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
    public function getId()
    {
        return $this->id;
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
}
