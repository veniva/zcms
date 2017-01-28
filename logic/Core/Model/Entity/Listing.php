<?php

namespace Logic\Core\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="\Logic\Core\Model\ListingRepository") @Table(name="listings")
 */
class Listing
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
     * @OneToMany(targetEntity="ListingContent", mappedBy="listing", cascade={"remove", "persist"})
     */
    protected $content;

    /**
     * @OneToMany(targetEntity="ListingImage", mappedBy="listing", cascade={"remove", "persist"})
     */
    protected $listingImages;

    /**
     * @ManyToMany(targetEntity="Category", inversedBy="listings")
     */
    protected $categories;

    public function __construct()
    {
        $this->content = new ArrayCollection();
        $this->listingImages = new ArrayCollection();
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

    public function getContent()
    {
        return $this->content;//v_todo - filter the content to show by lang.status to be able to implement language.status = inactive
    }

    /**
     * @param null $langId If null, then an entity of the default language is returned
     * @return ListingContent|null
     */
    public function getSingleListingContent($langId = null)
    {
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

    public function addContent(ListingContent $content)
    {
        $this->content[] = $content;
    }

    /**
     * @return ListingImage|null The first ListingImage entity from the collection, or null
     */
    public function getListingImage()
    {
        return isset($this->listingImages[0]) ? $this->listingImages[0] : null;
    }

    /**
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function getListingImages()
    {
        return $this->listingImages;
    }

    /**
     * @param ArrayCollection $listingImages
     */
    public function setListingImages(ArrayCollection $listingImages)
    {
        $this->listingImages = $listingImages;
    }

    public function addListingImage(ListingImage $listingImage)
    {
        $this->listingImages[] = $listingImage;
    }

    /**
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * This serves to set a category as the only parent category
     * @param Category $category
     */
    public function setOnlyCategory(Category $category)
    {
        $this->categories = new ArrayCollection([$category]);
    }
}
