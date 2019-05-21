<?php

namespace AppBundle\Controller\Front;

use AppBundle\Entity\Parameter;
use AppBundle\Entity\Post;
use AppBundle\Form\CategoryType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Category;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 * @Route("/categories", name="front_category_")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction($id)
    {
        $catRepos = $this->getDoctrine()->getRepository(Category::class);
        $sub_categories = $catRepos->findByParent($id);

        if (!$sub_categories) {
            return $this->redirectToRoute('front_post_index', ['category_id' => $id]);
        }

        $postRepos = $this->getDoctrine()->getRepository(Post::class);
        $categories = [];
        foreach ($sub_categories as $category) {
            $categories []= [
                'sub_category' => $category,
                'posts_count' => $posts_count = $postRepos->getCountByCategory($category->getId(), true),
            ];
        }

        return $this->render('front/category/show.html.twig', [
            'categories' => $categories,
        ]);
    }


}
