<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * OptionName
 *
 * @ORM\Table(name="option_name")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OptionNameRepository")
 */
class OptionName
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
     * @var int
     *
     * @ORM\Column(name="integer_type", type="integer", nullable=true)
     */
    private $integerType;

    /**
     * @var string
     *
     * @ORM\Column(name="string_type", type="string", length=50, nullable=true)
     */
    private $stringType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_type", type="date", nullable=true)
     */
    private $dateType;

    /**
     * @ORM\ManyToOne(targetEntity="Parameter", inversedBy="options")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id")
     */
    private $parameter;

    /**
     * @ORM\ManyToMany(targetEntity="Post", mappedBy="options", cascade={"persist"})
     * @ORM\JoinTable(name="option_post")
     */
    private $posts;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function __toString()
    {
        return (string)$this->getId();
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
     * Set integerType
     *
     * @param integer $integerType
     *
     * @return OptionName
     */
    public function setIntegerType($integerType)
    {
        $this->integerType = $integerType;

        return $this;
    }

    /**
     * Get integerType
     *
     * @return int
     */
    public function getIntegerType()
    {
        return $this->integerType;
    }

    /**
     * Set stringType
     *
     * @param string $stringType
     *
     * @return OptionName
     */
    public function setStringType($stringType)
    {
        $this->stringType = $stringType;

        return $this;
    }

    /**
     * Get stringType
     *
     * @return string
     */
    public function getStringType()
    {
        return $this->stringType;
    }

    /**
     * Set dateType
     *
     * @param \DateTime $dateType
     *
     * @return OptionName
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;

        return $this;
    }

    /**
     * Get dateType
     *
     * @return \DateTime
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * Set parameter
     *
     * @param integer $parameter
     *
     * @return OptionName
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get parameter
     *
     * @return int
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @return ArrayCollection|Post[]
     */
    public function getPosts()
    {
        return $this->posts;
    }

    public function addPost(Post $post)
    {
        if ($this->posts->contains($post)) {
            return;
        }

        $this->posts[] = $post;
        $post->addOption($this);
    }

    public function removePost(Post $post)
    {
        if (!$this->posts->contains($post)) {
            return;
        }

        $this->posts->removeElement($post);
        $post->removeOption($this);
    }

    public function getName()
    {
        if ($this->getParameter()->getType() == 'Integer') {
            return $this->getIntegerType();
        } elseif ($this->getParameter()->getType() == 'String') {
            return $this->getStringType();
        } elseif ($this->getParameter()->getType() == 'Date') {
            return $this->getDateType();
        }
    }

    public function setName($type, $value)
    {
        if ($type == 'Integer') {
            return $this->setIntegerType($value);
        } elseif ($type == 'String') {
            return $this->setStringType($value);
        } elseif ($type == 'Date') {
            return $this->setDateType($value);
        }
    }
}

