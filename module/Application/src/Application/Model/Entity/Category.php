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
     * @OneToMany(targetEntity="CategoryContent", mappedBy="category")
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
     * @param null $langId If null an entity on the default language is returned; if false a collection of all entities is returned
     * @return \Application\Model\Entity\CategoryContent|\Doctrine\ORM\PersistentCollection Either a single entity or a collection of entities
     */
    public function getContent($langId = null)
    {
        if(is_null($langId))
            $langId = Misc::getDefaultLanguageID();
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
}
