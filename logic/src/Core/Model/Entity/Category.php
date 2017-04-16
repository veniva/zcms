<?php
namespace Logic\Core\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="\Logic\Core\Model\CategoryRepository") @Table(name="categories")
 */
class Category
{
    /**
     * @Id @GeneratedValue @Column(type="integer", options={"unsigned": true})
     */
    protected $id;

    /**
     * @Column(type="integer", options={"default": 0})
     */
    protected $sort = 0;

    /**
     * @ManyToMany(targetEntity="Category", cascade={"persist"})
     * @JoinTable(name="category_parents",
     *      joinColumns={@JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="parent_id", referencedColumnName="id")}
     *      )
     */
    protected $parents;

    /**
     * @OneToMany(targetEntity="Category", mappedBy="parent", cascade={"remove", "persist"})
     */
    protected $children;

    /**
     * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#one-to-many-self-referencing
     * Many categories have one category - adjacency approach
     * @ManyToOne(targetEntity="Category", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent = null;

    /**
     * @OneToMany(targetEntity="CategoryContent", mappedBy="category", cascade={"remove", "persist"})
     */
    protected $content;

    /**
     * @ManyToMany(targetEntity="Listing", mappedBy="categories", cascade={"remove", "persist"})
     */
    protected $listings;

    protected $isContentSorted = false;

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

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return null|Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Category $category
     */
    public function setParent(Category $category)
    {
        $this->parent = $category;
    }

    public function getParentId()
    {
        return $this->getParent() ? $this->getParent()->getId() : null;
    }

    public function getContent()
    {
        //$this->sortContent();//v_todo - this might create a db inconsistency - sometimes the wrong CategoryContent is being edited
        return $this->content;//return collection of content
    }

    /**
     * Sort the Category content according to the language status - the default language first.
     */
    public function sortContent()
    {
        if(!$this->isContentSorted){
            $categoryContentStatus = [];
            foreach($this->content as $content){
                if(!array_key_exists($content->getId(), $categoryContentStatus)){
                    $categoryContentStatus[$content->getId()] = $content->getLang()->getStatus();
                }
            }
            arsort($categoryContentStatus);
            $contentCollection = clone $this->content;
            $this->content->clear();
            foreach($categoryContentStatus as $cID => $status){
                foreach($contentCollection as $content){
                    if($content->getId() == $cID) $this->content->add($content);
                }
            }
            $this->isContentSorted = true;
        }
    }

    /**
     * @param null $langId
     * @return CategoryContent|bool
     * @throws \InvalidArgumentException
     */
    public function getSingleCategoryContent($langId)
    {
        if(!$langId) throw new \InvalidArgumentException('Invalid parameter $langId');

        foreach($this->content as $content){
            if($content->getLang()->getId() == $langId){
                return $content;//return single entity
            }
        }

        return false;
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
