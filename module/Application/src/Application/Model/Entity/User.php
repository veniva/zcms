<?php

namespace Application\Model\Entity;

/**
 * Class User
 * @Entity @Table(name="users")
 */
class User
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $uname;

    /**
     * @Column(type="string")
     */
    protected $upass;

    /**
     * @Column(type="string")
     */
    protected $email;

    /**
     * @Column(type="integer")
     */
    protected $type = 1;

    /**
     * @Column(type="datetime", name="reg_date")
     */
    protected $regDate;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUname()
    {
        return $this->uname;
    }

    /**
     * @param mixed $uname
     */
    public function setUname($uname)
    {
        $this->uname = $uname;
    }

    /**
     * @return mixed
     */
    public function getUpass()
    {
        return $this->upass;
    }

    /**
     * @param mixed $upass
     */
    public function setUpass($upass)
    {
        $this->upass = $upass;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getRegDate()
    {
        return $this->regDate;
    }

    /**
     * @param mixed $regDate
     */
    public function setRegDate($regDate)
    {
        $this->regDate = $regDate;
    }
}
