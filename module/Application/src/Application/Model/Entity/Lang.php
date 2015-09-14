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
     * @Column(type="integer", name="front_end")
     */
    protected $frontEnd;

    /**
     * @Column(type="integer", name="back_end")
     */
    protected $backEnd;

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
    public function getFrontEnd()
    {
        return $this->frontEnd;
    }

    /**
     * @param integer $frontEnd
     */
    public function setFrontEnd($frontEnd)
    {
        $this->frontEnd = $frontEnd;
    }

    /**
     * @return integer
     */
    public function getBackEnd()
    {
        return $this->backEnd;
    }

    /**
     * @param integer $backEnd
     */
    public function setBackEnd($backEnd)
    {
        $this->backEnd = $backEnd;
    }
}
