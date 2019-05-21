<?php

namespace AppBundle\Controller\Front;

//use AppBundle\Entity\Parameter;
use AppBundle\Entity\Image;
use AppBundle\Entity\OptionName;
use AppBundle\Entity\Parameter;
use AppBundle\Form\PostType;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotNull;


/**
 * @Route("/posts", name="front_post_")
 */

class PostController extends Controller
{
    /**
     * @Route("/{category_id}", name="index")
     */
    public function indexAction($category_id, Request $request, PaginatorInterface $paginator)
    {
//        $options = $this->getDoctrine()->getRepository(Post::class)->findByOptions([246,248,249]);
//        $options = $this->getDoctrine()->getRepository(OptionName::class)->findByMinMaxValue(0, 250, true);
//        $period = [
//            'min_datetime' => date('Y-m-d', strtotime('-3 days')),
//            'max_datetime' => date('Y-m-d'),
//        ];
//        $category = $this->getDoctrine()->getRepository(Post::class)->findByOptions([244], false, false, $period);
//        dump($category);
//        die;


        $posts_result = null;
        $filter_options_result = null;
        if ($request->isMethod('post')) {
            $parameters = $request->request->all()['parameters'];
            $filter_options_result = $parameters;
            $posts_result = $this->getFilteredPosts($parameters, $category_id);
        } else {
            $posts_result = $this->getDoctrine()->getRepository(Post::class)->findByCategory($category_id, true);
        }

        $pagination = $paginator->paginate(
            $posts_result,
            $request->query->getInt('page', 1),
            20
        );

        $options = [];
        foreach ($posts_result as $post) {
            foreach ($post->getOptions() as $option) {
                $options[$post->getId()][$option->getParameter()->getId()] = $option;
            }
        }

        $category = $this->getDoctrine()->getRepository(Category::class)->find($category_id);
        return $this->render('front/post/index.html.twig', [
            'pagination' => $pagination,
            'filter_parameters' => $this->getDoctrine()->getRepository(Parameter::class)->getByCategory($category, 'filters', 'filters'),
            'column_parameters' => $this->getDoctrine()->getRepository(Parameter::class)->getByCategory($category, 'columns', 'columns'),
            'options' => $options,
            'category_id' => $category_id,
            'filter_options' => $filter_options_result,
        ]);
    }

    public function getFilteredPosts($filters, $category_id)
    {
        $optionRep = $this->getDoctrine()->getRepository(OptionName::class);
        $postRep = $this->getDoctrine()->getRepository(Post::class);
        $posts_result = [];

        $period = false;
        if (isset($filters['period']) && $filters['period'] != '') {
            $min_datetime = date('Y-m-d', strtotime('-'.$filters['period'].' days'));
            $period = [
                'min_datetime' => $min_datetime,
                'max_datetime' => date('Y-m-d'),
            ];
        }

        $posts_request = [];
        $is_parameters_empty = true;
        foreach ($filters as $key => $parameter) {
            $min_value = '';
            $max_value = '';
            $option_ids = false;

            if (isset($parameter['value']) && $parameter['value']) {
                $option = $optionRep->findByName($parameter['value'], $key);
                $option_ids = $option ? (array)$option->getId() : [];
                $is_parameters_empty = false;
            } else {
                if (isset($parameter['min_value']) && $parameter['min_value']) {
                    $min_value = $parameter['min_value'];
                }
                if (isset($parameter['max_value']) && $parameter['max_value']) {
                    $max_value = $parameter['max_value'];
                }
                if ($min_value || $max_value) {
                    $option_ids = $optionRep->findByMinMaxValue($key, $min_value, $max_value, true);
                    $is_parameters_empty = false;
                }
            }

            if ($option_ids) {
                $posts_request[$key] = $postRep->findByOptions($option_ids, $category_id, 1, $period);
            } elseif ($option_ids !== false && empty($option_ids)) {
//                $posts_request[$key] = [];
                return $posts_result;
            }
        }

        if ($is_parameters_empty) {
            $posts_result = $postRep->findByCategory($category_id, true, $period);
        } else {
            if ($posts_request) {
//                $is_param_posts_empty = false;
//                foreach ($posts_request as $param_posts) {
//                    if (!$param_posts) {
//                        $is_param_posts_empty = true;
//                    }
//                }
//                if ($is_param_posts_empty) {
//                    return [];
//                }

                $first_posts = array_shift($posts_request);
                while ($posts_request) {
                    $second_posts = array_shift($posts_request);
                    foreach ($first_posts as $key => $frst_post) {
                        $to_delete = true;
                        foreach ($second_posts as $scnd_post) {
                            if ($frst_post->getId() == $scnd_post->getId()) {
                                $to_delete = false;
                            }
                        }
                        if ($to_delete) {
                            unset($first_posts[$key]);
                        }
                    }
                }
                $posts_result = $first_posts;
            }
        }

        return $posts_result;
    }

    /**
     * @Route("/post/{id}", name="show", methods={"GET"})
     */
    public function showAction($id, Post $post)
    {
        $options = $post->getOptions();
        $options_res = [];
        foreach ($options as $option) {
            $options_res[$option->getParameter()->getId()] []= $option;
        }

        $category = $this->getDoctrine()->getRepository(Category::class)->find($post->getCategory()->getId());
        return $this->render('front/post/show.html.twig', [
            'post' => $post,
            'parameters' => $this->getDoctrine()->getRepository(Parameter::class)->getByCategory($category, 'posts', 'posts'),
            'options' => $options_res
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function newAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

//        if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted()) {
            $data = $request->request->all();

//            dump($data);
//            die;

            if (isset($data['post']['parameters']) && count($data['post']['parameters']) > 0) {
                foreach ($data['post']['parameters'] as $key => $options) {
                    $parameter = $this->getDoctrine()->getRepository(Parameter::class)->find($key);
                    if ($parameter) {
                        if ($options && count($options) > 0) {
                            foreach ($options as $idOrName) {
                                if ($parameter->getTag() == 'Input') {
                                    $option = $this->getDoctrine()->getRepository(OptionName::class)->findByName($idOrName, $parameter->getId());
                                    if (!$option) {
                                        $option = new OptionName();
                                        $option->setParameter($parameter);
                                        $option->setName($parameter->getType(), $idOrName);
                                        $entityManager->persist($option);
                                    }
                                    $post->addOption($option);
                                } else {
                                    $option = $this->getDoctrine()->getRepository(OptionName::class)->find($idOrName);
                                    $post->addOption($option);
                                }
                            }
                        }
                    }
                }
            }

            $category = $entityManager->getRepository(Category::class)->find($data['post']['category']);
            $post->setCategory($category);

            $files_manager = $this->get('oneup_uploader.orphanage_manager')->get('gallery');
            $files = $files_manager->uploadFiles();
            if ($files) {
                foreach ($files as $key => $file) {
                    $image = new Image();
                    $image->setName($file->getFilename());
                    $image->setPath('uploads/gallery/'.$file->getFilename());
                    $is_main = 0;
                    if ($key == 0) {
                        $is_main = 1;
                    }
                    $image->setIsMain($is_main);
                    $entityManager->persist($image);
                    $post->addImage($image);
                }
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post successfully added');
            return $this->redirectToRoute('admin_post_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to add post');
        }
        return $this->render('admin/post/new.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
            'post' => $post,
            'form' => $form->createView(),
        ]);

    }


}
