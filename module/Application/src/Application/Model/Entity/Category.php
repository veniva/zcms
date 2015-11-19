<?php
namespace Application\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Invokable\Misc;
use Zend\Form\Annotation;

/**
 * @Annotation\Name("category")
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 *
 * @Entity(repositoryClass="\Application\Model\CategoryRepository") @Table(name="categories")
 */
class Category
{
    /**
     * @Annotation\Exclude()
     *
     * @Id @GeneratedValue @Column(type="integer", options={"unsigned": true})
     */
    protected $id;

    /**
     * @Annotation\Exclude()
     *
     * @Column(type="integer", options={"comment":"Category type. 1 - pages, 2 - listings, 3 ..."})
     */
    protected $type = 1;

    /**
     * @Annotation\Type("number")
     * @Annotation\Validator({"name": "Digits"})
     * @Annotation\Options({"label": "Sort"})
     * @Annotation\Attributes({"maxlength": 3, "class": "numbers"})
     *
     * @Column(type="integer", options={"default": 0})
     */
    protected $sort = 0;

    /**
     * @Annotation\Exclude()
     *
     * @ManyToMany(targetEntity="Category", cascade={"remove", "persist"})
     * @JoinTable(name="category_children",
     *      joinColumns={@JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="child_id", referencedColumnName="id")}
     *      )
     */
    protected $children;

    /**
     * @Annotation\Exclude()
     *
     * @ManyToMany(targetEntity="Category", cascade={"persist"})
     * @JoinTable(name="category_parents",
     *      joinColumns={@JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="parent_id", referencedColumnName="id")}
     *      )
     */
    protected $parents;

    /**
     * @Annotation\Exclude()
     *
     * @Column(type="integer", name="parent_id", options={"unsigned": true}, nullable=true)
     */
    protected $parent;

    /**
     * @Annotation\Exclude()
     *
     * @OneToMany(targetEntity="CategoryContent", mappedBy="category", cascade={"remove", "persist"})
     */
    protected $content;

    /**
     * @Annotation\Exclude()
     *
     * @ManyToMany(targetEntity="Listing", mappedBy="categories", cascade={"remove", "persist"})
     */
    protected $listings;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->listings = new ArrayCollection();
        $this->content = new ArrayCollection();
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
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int $parentId
     */
    public function setParent($parentId)
    {
        $this->parent = $parentId;
    }

    public function getContent()
    {
        return $this->content;//return collection of content v_todo - sort by language status
    }

    /**
     * @param null $langId If null, then an entity on the default language is returned
     * @return CategoryContent|null
     */
    public function getSingleCategoryContent($langId = null)
    {
        if(is_null($langId) && Misc::getDefaultLanguage())
            $langId = Misc::getDefaultLanguage()->getId();
        //return a content in concrete language only if desired
        if($langId){
            foreach($this->content as $content){
                if($content->getLang()->getId() == $langId){
                    return $content;//return single entity
                }
            }
        }

        return null;
    }

    public function addCategoryContent(CategoryContent $categoryContent)
    {
        $this->content[] = $categoryContent;
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
    public function setChildren(ArrayCollection $children)
    {
        $this->children = $children;
    }

    public function addChild(Category $child)
    {
        $this->children[] = $child;
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
    public function setParents(ArrayCollection $parents)
    {
        $this->parents = $parents;
    }

}
