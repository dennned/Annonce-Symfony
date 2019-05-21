<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Parameter
 *
 * @ORM\Table(name="parameter")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ParameterRepository")
 */
class Parameter
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
     * @ORM\OneToMany(targetEntity="OptionName", mappedBy="parameter", cascade={ "persist", "remove"}, orphanRemoval=true)
     */
    private $options;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="parameters")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="name_en", type="string", length=100)
     */
    private $nameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="name_fr", type="string", length=100)
     */
    private $nameFr;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=50)
     */
    private $tag;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_filter", type="boolean")
     */
    private $isFilter;

    /**
     * @var int
     *
     * @ORM\Column(name="filters_index", type="integer")
     */
    private $filtersIndex;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_column", type="boolean")
     */
    private $isColumn;

    /**
     * @var int
     *
     * @ORM\Column(name="columns_index", type="integer")
     */
    private $columnsIndex;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_post", type="boolean")
     */
    private $isPost;

    /**
     * @var int
     *
     * @ORM\Column(name="posts_index", type="integer")
     */
    private $postsIndex;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_range", type="boolean")
     */
    private $isRange;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_bold", type="boolean")
     */
    private $isBold;

//    /**
//     * @var int
//     *
//     * @ORM\Column(name="is_custom", type="integer", length=1)
//     */
//    private $is_custom;

//    /**
//     * @ORM\ManyToMany(targetEntity="Category", mappedBy="parameters")
//     * @ORM\JoinTable(name="category_parameter")
//     */
//    private $categories;

//    /**
//     * @ORM\ManyToMany(targetEntity="Post", mappedBy="parameters")
//     * @ORM\JoinTable(name="parameter_post")
//     */
//    private $posts;

//    /**
//     * @ORM\OneToMany(targetEntity="CategoryParameter", mappedBy="parameter", cascade={ "persist", "remove"}, orphanRemoval=true)
//     */
//    private $category_parameters;


    public function __construct()
    {
//        $this->categories = new ArrayCollection();
//        $this->posts = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function __toString(){
        return $this->getName();
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
     * Set type
     *
     * @param string $type
     *
     * @return Parameter
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

//    /**
//     * @return int
//     */
//    public function getIsCustom()
//    {
//        return $this->is_custom;
//    }
//
//    /**
//     * @param int $is_custom
//     */
//    public function setIsCustom($is_custom)
//    {
//        $this->is_custom = $is_custom;
//    }

//    /**
//     * @return ArrayCollection|Category[]
//     */
//    public function getCategories()
//    {
//        return $this->categories;
//    }
//
//    public function addCategory(Category $category)
//    {
//        if ($this->categories->contains($category)) {
//            return;
//        }
//
//        $this->categories[] = $category;
//        $category->addParameter($this);
//    }
//
//    public function removeCategory(Category $category)
//    {
//        $this->categories->removeElement($category);
//        $category->removeParameter($this);
//    }

//    /**
//     * @return ArrayCollection|Post[]
//     */
//    public function getPosts()
//    {
//        return $this->posts;
//    }
//
//    public function addPost(Post $post)
//    {
//        if ($this->posts->contains($post)) {
//            return;
//        }
//
//        $this->posts[] = $post;
//        $post->addParameter($this);
//    }
//
//    public function removePost(Post $post)
//    {
//        if (!$this->posts->contains($post)) {
//            return;
//        }
//
//        $this->posts->removeElement($post);
//        $post->removeParameter($this);
//    }

    /**
     * Get options
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Add option
     *
     * @param OptionName $option
     *
     * @return Parameter
     */
    public function addOption(OptionName $option)
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setParameter($this);
        }

        return $this;
    }

    /**
     * Remove option
     *
     * @param OptionName $option
     */
    public function removeOption(OptionName $option)
    {
        if ($this->options->contains($option)) {
            $this->options->removeElement($option);
            // set the owning side to null (unless already changed)
            if ($option->getParameter() === $this) {
                $option->setParameter(null);
            }
        }
    }

//    /**
//     * Get category_parameters
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getCategoryParameters()
//    {
//        return $this->category_parameters;
//    }
//
//    /**
//     * Add category_parameter
//     *
//     * @param CategoryParameter $category_parameter
//     *
//     * @return Parameter
//     */
//    public function addCategoryParameter(CategoryParameter $category_parameter)
//    {
//        if (!$this->category_parameters->contains($category_parameter)) {
//            $this->category_parameters[] = $category_parameter;
//            $category_parameter->setParameter($this);
//        }
//
//        return $this;
//    }
//
//    /**
//     * Remove category_parameter
//     *
//     * @param CategoryParameter $category_parameter
//     */
//    public function removeCategoryParameter(CategoryParameter $category_parameter)
//    {
//        if ($this->category_parameters->contains($category_parameter)) {
//            $this->category_parameters->removeElement($category_parameter);
//            // set the owning side to null (unless already changed)
//            if ($category_parameter->getParameter() === $this) {
//                $category_parameter->setParameter(null);
//            }
//        }
//    }

    /**
     * Set categoryId
     *
     * @param integer $category
     *
     * @return Parameter
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

    /**
     * @return bool
     */
    public function getIsFilter()
    {
        return $this->isFilter;
    }

    /**
     * @param bool $isFilter
     */
    public function setIsFilter($isFilter)
    {
        $this->isFilter = $isFilter;
    }

    /**
     * @return bool
     */
    public function getIsColumn()
    {
        return $this->isColumn;
    }

    /**
     * @param bool $isColumn
     */
    public function setIsColumn($isColumn)
    {
        $this->isColumn = $isColumn;
    }

    /**
     * @return bool
     */
    public function getIsPost()
    {
        return $this->isPost;
    }

    /**
     * @param bool $isPost
     */
    public function setIsPost($isPost)
    {
        $this->isPost = $isPost;
    }

    /**
     * @return bool
     */
    public function getIsRange()
    {
        return $this->isRange;
    }

    /**
     * @param bool $isRange
     */
    public function setIsRange($isRange)
    {
        $this->isRange = $isRange;
    }

    /**
     * @return int
     */
    public function getFiltersIndex()
    {
        return $this->filtersIndex;
    }

    /**
     * @param int $filtersIndex
     */
    public function setFiltersIndex($filtersIndex)
    {
        $this->filtersIndex = $filtersIndex;
    }

    /**
     * @return int
     */
    public function getColumnsIndex()
    {
        return $this->columnsIndex;
    }

    /**
     * @param int $columnsIndex
     */
    public function setColumnsIndex($columnsIndex)
    {
        $this->columnsIndex = $columnsIndex;
    }

    /**
     * @return int
     */
    public function getPostsIndex()
    {
        return $this->postsIndex;
    }

    /**
     * @param int $postsIndex
     */
    public function setPostsIndex($postsIndex)
    {
        $this->postsIndex = $postsIndex;
    }

    /**
     * @return bool
     */
    public function getIsBold()
    {
        return $this->isBold;
    }

    /**
     * @param bool $isBold
     */
    public function setIsBold($isBold)
    {
        $this->isBold = $isBold;
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


}

