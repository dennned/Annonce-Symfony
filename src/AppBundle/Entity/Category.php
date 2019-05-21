<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Locale;

/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category
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
     * @ORM\Column(name="name_en", type="string", length=190)
     */
    private $nameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="name_fr", type="string", length=190)
     */
    private $nameFr;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

//    /**
//     * @ORM\ManyToMany(targetEntity="Parameter", inversedBy="categories")
//     * @ORM\JoinTable(name="category_parameter")
//     */
//    private $parameters;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="category", cascade={ "persist", "remove"}, orphanRemoval=true)
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="Parameter", mappedBy="category", cascade={ "persist", "remove"}, orphanRemoval=true)
     */
    private $parameters;

    /**
     * @ORM\OneToOne(targetEntity="Image", mappedBy="category", cascade={ "persist", "remove"}, orphanRemoval=true)
     */
    private $image;


    public function __construct()
    {
//        $this->parameters = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->parameters = new ArrayCollection();
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        if(isset($GLOBALS['request']) && $GLOBALS['request']) {
            $locale = $GLOBALS['request']->getLocale();
//            return $this->{'getName'.ucfirst($locale).'()'}; // getNameFr()

            if ($locale == 'fr' && $this->getNameFr()) {
                return $this->getNameFr();
            }
        }

        if ($this->getNameEn()) {
            return $this->getNameEn();
        } else {
            return $this->getNameFr();
        }
    }

    /**
     * Set parent
     *
     * @param integer $parent
     *
     * @return Category
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function __toString()
    {
        return $this->getName();
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
//        $parameter->addCategory($this);
//    }
//
//    public function removeParameter(Parameter $parameter)
//    {
//        if (!$this->parameters->contains($parameter)) {
//            return;
//        }
//
//        $this->parameters->removeElement($parameter);
//        $parameter->removeCategory($this);
//    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Add post
     *
     * @param Post $post
     *
     * @return Category
     */
    public function addPost(Post $post)
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove post
     *
     * @param Post $post
     */
    public function removePost(Post $post)
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }
    }

    /**
     * Get parameters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Add parameter
     *
     * @param Parameter $parameter
     *
     * @return Category
     */
    public function addParameter(Parameter $parameter)
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
            $parameter->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove parameter
     *
     * @param Parameter $parameter
     */
    public function removeParameter(Parameter $parameter)
    {
        if ($this->parameters->contains($parameter)) {
            $this->parameters->removeElement($parameter);
            // set the owning side to null (unless already changed)
            if ($parameter->getCategory() === $this) {
                $parameter->setCategory(null);
            }
        }
    }

    /**
     * @return string
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * @param string $nameEn
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
    }

    /**
     * @return string
     */
    public function getNameFr()
    {
        return $this->nameFr;
    }

    /**
     * @param string $nameFr
     */
    public function setNameFr($nameFr)
    {
        $this->nameFr = $nameFr;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
        $image->setCategory($this);
    }
}

