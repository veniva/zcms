<?php

namespace Application\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Invokable\Misc;
use Zend\Form\Annotation;

/**
 * @Annotation\Name("listing")
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 *
 * @Entity(repositoryClass="\Application\Model\ListingRepository") @Table(name="listings")
 */
class Listing
{
    /**
     * @Annotation\Exclude()
     *
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Annotation\Type("number")
     * @Annotation\Validator({"name": "Digits"})
     * @Annotation\Options({"label": "Sort"})
     * @Annotation\Attributes({"maxlength": 3, "class": "numbers"})
     *
     * @Column(type="integer")
     */
    protected $sort;

    /**
     * @Annotation\Exclude()
     *
     * @OneToMany(targetEntity="ListingContent", mappedBy="listing", cascade={"remove", "persist"})
     */
    protected $content;

    /**
     * A collection of metadata entities in different languages
     * @Annotation\Exclude()
     *
     * @OneToMany(targetEntity="Metadata", mappedBy="listing", cascade={"remove", "persist"})
     */
    protected $metadata;

    /**
     * @Annotation\Exclude()
     *
     * @OneToMany(targetEntity="ListingImage", mappedBy="listing", cascade={"remove", "persist"})
     */
    protected $listingImages;

    /**
     * @Annotation\Exclude()
     *
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

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param null $langId If null, then an entity of the default language is returned
     * @return ListingContent|null
     */
    public function getSingleListingContent($langId = null)
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
        return null;
    }

    public function addContent(ListingContent $content)
    {
        $this->content[] = $content;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param null $langId If null, then an entity of the default language is returned
     * @return Metadata|null
     */
    public function getSingleMetadata($langId = null)
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
