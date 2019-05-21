<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * PasswordRecovery
 *
 * @ORM\Table(name="password_recoveries")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PasswordRecoveryRepository")
 */
class PasswordRecovery
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="passwordRecoveries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="security_key", type="string", length=100)
     */
    private $securityKey;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
    }



    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set securityKey
     *
     * @param string $securityKey
     *
     * @return PasswordRecovery
     */
    public function setSecurityKey($securityKey)
    {
        $this->securityKey = $securityKey;

        return $this;
    }

    /**
     * Get securityKey
     *
     * @return string
     */
    public function getSecurityKey()
    {
        return $this->securityKey;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}

