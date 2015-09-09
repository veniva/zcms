<?php

namespace Application\Model\Entity;

/**
 * Class Lang
 * @Entity @Table(name="lang")
 */
class Lang
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $iso_code;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="integer")
     */
    protected $front_end;

    /**
     * @Column(type="integer")
     */
    protected $back_end;

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
        return $this->iso_code;
    }

    /**
     * @param string $iso_code
     */
    public function setIsoCode($iso_code)
    {
        $this->iso_code = $iso_code;
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
        return $this->front_end;
    }

    /**
     * @param integer $front_end
     */
    public function setFrontEnd($front_end)
    {
        $this->front_end = $front_end;
    }

    /**
     * @return integer
     */
    public function getBackEnd()
    {
        return $this->back_end;
    }

    /**
     * @param integer $back_end
     */
    public function setBackEnd($back_end)
    {
        $this->back_end = $back_end;
    }
}
