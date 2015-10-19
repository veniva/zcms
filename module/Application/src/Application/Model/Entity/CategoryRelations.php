<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 28/08/2015
 * Time: 14:30
 */

namespace Application\Model\Entity;

/**
 * @Entity @Table(name="category_rel")
 */
class CategoryRelations
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="relatedParents")
     */
    protected $category;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="relatedChildren")
     */
    protected $parent;

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
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}
