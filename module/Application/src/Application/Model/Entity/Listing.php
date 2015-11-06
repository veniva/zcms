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
     * A collection of metadata entities in different languages
     * @OneToMany(targetEntity="Metadata", mappedBy="listing", cascade={"remove", "persist"})
     */
    protected $metadata;

    /**
     * @OneToMany(targetEntity="ListingImage", mappedBy="listing", cascade={"remove", "persist"})
     */
    protected $listingImages;

    /**
     * @ManyToMany(targetEntity="Category", inversedBy="listings", cascade={"remove", "persist"})
     */
    protected $categories;

    public function __construct()
    {
        $this->content = new ArrayCollection();
        $this->metadata = new ArrayCollection();
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

    public function addContent(ListingContent $content)
    {
        $this->content[] = $content;
    }

    public function getMetadata($langId = null)
    {
        if(is_null($langId))
            $langId = Misc::getDefaultLanguage()->getId();
        //return a content in concrete language only if desired
        if($langId){
            foreach($this->metadata as $metadata){
                if($metadata->getLang()->getId() == $langId){
                    return $metadata;//return single entity
                }
            }
        }
        return $this->metadata;
    }

    /**
     * @param Metadata $metadata
     */
    public function addMetadata(Metadata $metadata)
    {
        $this->metadata[] = $metadata;
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
