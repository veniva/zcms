<?php

namespace Logic\Core\Model\Entity;

/**
 * Class Lang
 * @Entity(repositoryClass="\Logic\Core\Model\LangRepository") @Table(name="lang")
 */
class Lang
{
    const STATUS_INACTIVE   = 0;
    const STATUS_ACTIVE     = 1;
    const STATUS_DEFAULT    = 2;

    /**
     * @Id @GeneratedValue @Column(type="integer", options={"unsigned": true})
     */
    protected $id;

    /**
     * @Column(type="string", length=2, name="iso_code", options={"fixed":true})
     */
    protected $isoCode;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="integer", options={"default": 1, "comment":"2- default; 1-active;"})
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * v_todo - remove
     * @OneToMany(targetEntity="CategoryContent", mappedBy="lang", cascade={"remove"})
     */
    protected $categoryContent;

    /**
     * v_todo - remove
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
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return $this
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
        return $this;
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusName()
    {
        return !is_null($this->status) ? $this->getStatusOptions()[$this->status] : null;
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'active',
            self::STATUS_DEFAULT => 'default'
        ];
    }

    /**
     * @deprecated - use self::isLanguageDefault() instead
     * 
     * Check's if the current entity's status, or the one provided as a parameter is the default language status
     * @param null|int $status Status number to be checked
     * @return bool
     */
    public function isDefault($status = null)
    {
        if($status && !is_numeric($status)) throw new \InvalidArgumentException('The provided argument is of invalid type');

        $status = $status ?: $this->status;
        $result = ($status == self::STATUS_DEFAULT) ? true : false;
        return $result;
    }
    
    public static function isLanguageDefault(int $status)
    {
        return $status === self::STATUS_DEFAULT;
    }
}
