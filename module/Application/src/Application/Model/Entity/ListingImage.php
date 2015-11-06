<?php

namespace Application\Model\Entity;

/**
 * @Entity @Table(name="listing_images")
 */
class ListingImage
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Listing", inversedBy="listingImages")
     */
    protected $listing;

    /**
     * @Column(type="string", name="image_name")
     */
    protected $imageName;

    public function __construct(Listing $listing = null)
    {
        if($listing)
            $this->setListing($listing);
    }

    /**
     * @return Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param Listing $listing
     */
    public function setListing(Listing $listing)
    {
        $listing->addListingImage($this);
        $this->listing = $listing;
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }
}
