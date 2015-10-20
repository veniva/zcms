<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 22/08/2015
 * Time: 16:15
 */

namespace Application\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Invokable\Misc;

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
    protected $type = 1;

    /**
     * @Column(type="integer")
     */
    protected $sort = 0;

    /**
     * @ManyToMany(targetEntity="Category", cascade={"remove", "persist"})
     * @JoinTable(name="category_rel",
     *      joinColumns={@JoinColumn(name="parent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     */
    protected $children;

    /**
     * @ManyToMany(targetEntity="Category", cascade={"persist"})
     * @JoinTable(name="category_rel",
     *      joinColumns={@JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="parent_id", referencedColumnName="id")}
     *      )
     */
    protected $parents;

    /**
     * @OneToOne(targetEntity="Category")
     */
    protected $parent;

    /**
     * @OneToMany(targetEntity="CategoryContent", mappedBy="category")
     */
    protected $content;

    /**
     * @ManyToMany(targetEntity="Listing", inversedBy="categories", cascade={"remove"})
     */
    protected $listings;

    public function __construct()
    {
        $this->listings = new ArrayCollection();
        $this->relatedParents = new ArrayCollection();
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
    public function setParent(Category $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param null $langId If null an entity on the default language is returned; if false a collection of all entities is returned
     * @return \Application\Model\Entity\CategoryContent|\Doctrine\ORM\PersistentCollection Either a single entity or a collection of entities
     */
    public function getContent($langId = null)
    {
        if(is_null($langId))
            $langId = Misc::getDefaultLanguage()->getId();
        //return a content in concrete language only if desired
        if($langId){
            foreach($this->content as $content){
                if($content->getLangId() == $langId){
                    return $content;//return single entity
                }
            }
        }

        return $this->content;//return collection of content
    }

    public function setCategoryContent(CategoryContent $categoryContent)
    {
        $this->content[] = $categoryContent;
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

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * @param mixed $parents
     */
    public function setParents($parents)
    {
        $this->parents = $parents;
    }

}
