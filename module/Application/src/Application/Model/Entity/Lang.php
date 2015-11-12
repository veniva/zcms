<?php

namespace Application\Model\Entity;

/**
 * Class Lang
 * @Entity(repositoryClass="\Application\Model\LangRepository") @Table(name="lang")
 */
class Lang
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string", name="iso_code")
     */
    protected $isoCode;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="integer")
     */
    protected $status;

    /**
     * @OneToMany(targetEntity="CategoryContent", mappedBy="lang", cascade={"remove"})
     */
    protected $categoryContent;

    /**
     * @OneToMany(targetEntity="ListingContent", mappedBy="lang", cascade={"remove"})
     */
    protected $listingContent;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatusName()
    {
        return !is_null($this->status) ? $this->getStatusOptions()[$this->status] : null;
    }

    public function getStatusOptions()
    {
        return [
            0 => 'inactive',
            1 => 'active',
            2 => 'default'
        ];
    }
}
