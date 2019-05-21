<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllInArray()
    {
        return $this->createQueryBuilder('c')
            ->getQuery()
            ->getArrayResult();
    }
    
	 public function getNoneEqualsIds($id){
        return $this->createQueryBuilder('c')->where("c.id !=".$id);
    }

    /**
     * @return Query Returns an array of Query objects
     */
    public function getQueryCategories()
    {
        return $this->createQueryBuilder('category')
            ->innerJoin('category.parent', 'parent')
            ->addSelect('parent')
            ->orderBy('category.id', 'DESC')
            ->getQuery();
    }

    public function findByParent($parent_id)
    {
        return $this->createQueryBuilder('category')
            ->join('category.parent', 'parent')
            ->where('parent.id = :parent_id')
            ->setParameter('parent_id', $parent_id)
            ->getQuery()
            ->getResult();
    }

    public function findAllChilds($parent_id, $return_simple_array = false)
    {
        $categories = $this->findByParent($parent_id);
        if (!$categories) {
            return null;
        }

        $result = [];
        $simple_array_res = [];
        foreach ($categories as $category) {
            if ($category->getId() == 0) {
                continue;
            }
            $childs_res = [];
            $childs = $this->findByParent($category->getId());
            if ($childs) {
                foreach ($childs as $child) {
                    $sub_childs_res = [];
                    $sub_childs = $this->findByParent($child->getId());
                    if ($sub_childs) {
                        foreach ($sub_childs as $sub_child) {
                            $sub_childs_res[$sub_child->getId()] = [
                                'category' => $sub_child,
                                'childs' => []
                            ];
                            $simple_array_res []= $sub_child;
                        }
                    }
                    $childs_res[$child->getId()] = [
                        'category' => $child,
                        'childs' => $sub_childs_res
                    ];
                    $simple_array_res []= $child;
                }
            }
            $result[$category->getid()] = [
                'category' => $category,
                'childs' => $childs_res
            ];
            $simple_array_res []= $category;
        }

        if ($return_simple_array) {
            return $simple_array_res;
        }

        return $result;
    }

}
