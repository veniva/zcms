<?php

namespace Application\Model\Entity;

/**
 * @Entity @Table(name="listings_content")
 */
class ListingContent
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Listing", inversedBy="content")
     */
    protected $listing;

    /**
     * @Column(type="integer", name="lang_id")
     */
    protected $langId;

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
     * @Column(type="string")
     */
    protected $text;

    public function getListing()
    {
        return $this->listing;
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
     * @return mixed
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * @param mixed $langId
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
    }
}
