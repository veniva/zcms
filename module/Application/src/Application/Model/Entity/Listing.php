<?php

namespace Application\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Invokable\Misc;

/**
 * Class Listing
 *
 * @Entity(repositoryClass="\Application\Model\ListingRepository") @Table(name="listings")
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
     * @OneToMany(targetEntity="ListingContent", mappedBy="listing", cascade={"remove", "persist"})
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

    public function getContent($langId = null)
    {
        if(is_null($langId))
            $langId = Misc::getDefaultLanguage()->getId();
        //return a content in concrete language only if desired
        if($langId){
            foreach($this->content as $content){
                if($content->getLang()->getId() == $langId){
                    return $content;//return single entity
                }
            }
        }
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
