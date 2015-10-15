<?php

namespace Application\Model\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Listing
 *
 * @Entity @Table(name="listings")
 */
class Listing
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="integer")
     */
    protected $sort;

    /**
     * @OneToOne(targetEntity="ListingContent", mappedBy="listing")
     * v_todo - amend this using oneToMany http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#annref-onetomany
     */
    protected $content;

    /**
     * @ManyToMany(targetEntity="Category", mappedBy="listings")
     */
    protected $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param integer $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function setToCategory(Category $category)
    {
        $this->categories[] = $category;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
