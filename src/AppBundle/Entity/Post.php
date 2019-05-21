<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Post
 *
 * @ORM\Table(name="posts")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 */
class Post
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=3000)
     */
    private $text;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_valid", type="boolean")
     */
    private $isValid;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="posts")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

//    /**
//     * @ORM\ManyToMany(targetEntity="Parameter", inversedBy="posts")
//     * @ORM\JoinTable(name="parameter_post")
//     */
//    private $parameters;

    /**
     * @ORM\ManyToMany(targetEntity="OptionName", inversedBy="posts")
     * @ORM\JoinTable(name="option_post")
     */
    private $options;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="post", cascade={ "persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $images;



    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
        $this->images = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getTitle();
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
     * Set title
     *
     * @param string $title
     *
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Post
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Post
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getStringCreatedAt()
    {
        return (string)$this->createdAt;
    }

    /**
     * Set user
     *
     * @param string $user
     *
     * @return Post
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set categoryId
     *
     * @param integer $category
     *
     * @return Post
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

//    /**
//     * @return ArrayCollection|Parameter[]
//     */
//    public function getParameters()
//    {
//        return $this->parameters;
//    }
//
//    public function addParameter(Parameter $parameter)
//    {
//        if ($this->parameters->contains($parameter)) {
//            return;
//        }
//
//        $this->parameters[] = $parameter;
//        $parameter->addPost($this);
//    }
//
//    public function removeParameter(Parameter $parameter)
//    {
//        if (!$this->parameters->contains($parameter)) {
//            return;
//        }
//
//        $this->parameters->removeElement($parameter);
//        $parameter->removePost($this);
//    }

    /**
     * @return ArrayCollection|OptionName[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function addOption(OptionName $option)
    {
        if ($this->options->contains($option)) {
            return;
        }

        $this->options[] = $option;
        $option->addPost($this);
    }

    public function removeOption(OptionName $option)
    {
        if (!$this->options->contains($option)) {
            return;
        }

        $this->options->removeElement($option);
        $option->removePost($this);
    }

    public function hasOption($option_id)
    {
        foreach ($this->options as $option) {
            if ($option->id == $option_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add image
     *
     * @param Image $image
     *
     * @return Post
     */
    public function addImage(Image $image)
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setPost($this);
        }

        return $this;
    }

    /**
     * Add image
     *
     * @param Image $image
     */
    public function removeImage(Image $image)
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getPost() === $this) {
                $image->setPost(null);
            }
        }
    }
}

