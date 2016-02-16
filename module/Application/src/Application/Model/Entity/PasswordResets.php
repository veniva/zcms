<?php

namespace Application\Model\Entity;

/**
 * @Entity(repositoryClass="\Application\Model\PasswordResetsRepository") @Table(name="password_resets")
 */
class PasswordResets
{
    /**
     * @Id @Column(type="text")
     */
    protected $email;

    /**
     * @Id @Column(type="text")
     */
    protected $token;

    /**
     * @Column(type="datetime", name="created_at", nullable=true)
     */
    protected $createdAt;

    public function __construct($email, $token)
    {
        $this->setEmail($email);
        $this->setToken($token);
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}