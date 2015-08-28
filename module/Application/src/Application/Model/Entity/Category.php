<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 22/08/2015
 * Time: 16:15
 */

namespace Application\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Category
 *
 * @Entity(repositoryClass="\Application\Model\CategoryRepository") @Table(name="categories")
 */
class Category
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="integer")
     */
    protected $type;

    /**
     * @Column(type="integer")
     */
    protected $sort;

    /**
     * @Column(type="integer")
     */
    protected $children_count;

    /**
     * @Column(type="integer", name="parent_id")
     */
    protected $parentId;

    /**
     * @OneToOne(targetEntity="CategoryContent", mappedBy="category")
     */
    protected $content;

    /**
     * @ManyToMany(targetEntity="Listing")
     */
    protected $listings;

    public function __construct()
    {
        $this->listings = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getChildrenCount()
    {
        return $this->children_count;
    }

    public function setChildrenCount($count)
    {
        $this->children_count = $count;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param mixed $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    public function setCategoryContent(CategoryContent $categoryContent)
    {
        $this->content = $categoryContent;
    }

    public function setToListing(Listing $listing)
    {
        $this->listings[] = $listing;
    }

    /**
     * @return mixed
     */
    public function getListings()
    {
        return $this->listings;
    }
}