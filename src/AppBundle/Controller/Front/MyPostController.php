<?php

namespace AppBundle\Controller\Front;

//use AppBundle\Entity\Parameter;
use AppBundle\Entity\Image;
use AppBundle\Entity\OptionName;
use AppBundle\Entity\Parameter;
use AppBundle\Form\MyPostType;
use AppBundle\Form\PostType;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotNull;


/**
 * @Route("/my_posts", name="front_my_post_")
 */

class MyPostController extends Controller
{
    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function indexAction(Request $request, PaginatorInterface $paginator)
    {

//        $test = $this->getDoctrine()->getRepository(OptionName::class)->findByName(2000, 117);
//        $key = substr(md5(mt_rand()), 0, 32);
//        $url = $this->generateUrl('password_recovery_restore', ['key' => $key], UrlGeneratorInterface::ABSOLUTE_URL);
//        dump($url);
//        die;


        $user = $this->getUser();
        $queryPosts = $this->getDoctrine()->getRepository(Post::class)->getQueryPosts($user->getId());

        $pagination = $paginator->paginate(
            $queryPosts,
            $request->query->getInt('page', 1),
            20
        );

        $categoriesRepository = $this->getDoctrine()->getRepository(Category::class);
        return $this->render('front/my_post/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categoriesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function newAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(MyPostType::class, $post);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');

//        if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted()) {
            $data = $request->request->all();

            if (isset($data['my_post']['parameters']) && count($data['my_post']['parameters']) > 0) {
                foreach ($data['my_post']['parameters'] as $key => $options) {
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

            $category = $entityManager->getRepository(Category::class)->find($data['my_post']['category']);
            $post->setCategory($category);
            $post->setIsValid(0);
            $post->setUser($this->getUser());

            $files_manager = $this->get('oneup_uploader.orphanage_manager')->get('gallery');
            $files = $files_manager->uploadFiles();
            if ($files) {
                foreach ($files as $key => $file) {
                    if ($key > 4) {
                        break;
                    }
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

            // send mail to admin
            $url = $this->generateUrl('admin_post_edit', ['id' => $post->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $to = 'denmakarenko@gmail.com'; // TODO
            $subject = 'Post Confirmation';
            $message = $translator->trans(
                'front.my_post.new.mail_admin',
                ['%url%' => $url]
            );
            mail($to, $subject, $message);

            $this->addFlash('success', $translator->trans('front.my_post.new.user_massage_success'));
            return $this->redirectToRoute('front_my_post_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', $translator->trans('front.my_post.new.user_massage_error'));
        }
        return $this->render('front/my_post/new.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
            'post' => $post,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction($id, Post $post)
    {
        $options = $post->getOptions();
        $options_res = [];
        foreach ($options as $option) {
            $options_res[$option->getParameter()->getId()] []= $option;
        }

        $category = $this->getDoctrine()->getRepository(Category::class)->find($post->getCategory()->getId());
        return $this->render('front/my_post/show.html.twig', [
            'post' => $post,
            'parameters' => $this->getDoctrine()->getRepository(Parameter::class)->getByCategory($category, 'posts', 'posts'),
            'options' => $options_res
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function editAction($id, Request $request, Post $post)
    {
        $form = $this->createForm(MyPostType::class, $post);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        $max_count_photos = 5;
        $count_uploaded_photos = count($post->getImages());
        $max_count_expected_photos = $max_count_photos - $count_uploaded_photos;

//        if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted()) {
            $postOptions = $entityManager->getRepository(OptionName::class)->getByPost($post->getId());
            foreach ($postOptions as $option) {
                $post->removeOption($option);
                $entityManager->persist($post);
            }

            $data = $request->request->all();

            if (isset($data['my_post']['parameters']) && count($data['my_post']['parameters']) > 0) {
                foreach ($data['my_post']['parameters'] as $key => $options) {
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

            $category = $entityManager->getRepository(Category::class)->find($data['my_post']['category']);
            $post->setCategory($category);
            $post->setIsValid(0);

            $files_manager = $this->get('oneup_uploader.orphanage_manager')->get('gallery');
            $files = $files_manager->uploadFiles();
            $have_main_image = $this->haveMainImage($id);
            if ($files) {
                foreach ($files as $key => $file) {
                    if ($max_count_expected_photos == 0) {
                        break;
                    }

                    $image = new Image();
                    $image->setName($file->getFilename());
                    $image->setPath('uploads/gallery/'.$file->getFilename());
                    $is_main = 0;
                    if ($key == 0 && !$have_main_image) {
                        $is_main = 1;
                    }
                    $image->setIsMain($is_main);
                    $entityManager->persist($image);
                    $post->addImage($image);

                    $max_count_expected_photos--;
                }
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post successfully updated and waiting for moderation');
            return $this->redirectToRoute('front_my_post_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to update post');
        }

        return $this->render('front/my_post/edit.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
            'post' => $post,
            'form' => $form->createView(),
            'max_count_photos' => $max_count_expected_photos
        ]);
    }

    public function haveMainImage($post_id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $post = $entityManager->getRepository(Post::class)->find($post_id);
        if ($post && $post->getImages()) {
            $count_main_images = 0;
            foreach ($post->getImages() as $image) {
                if ($image->getIsMain()) {
                    $count_main_images++;
                }
            }

            if ($count_main_images == 0) {
                return false;
            }

            if ($count_main_images > 1) {
                foreach ($post->getImages() as $image) {
                    if ($count_main_images == 1) {
                        break;
                    }
                    if ($image->getIsMain()) {
                        $image->setIsMain(0);
                        $entityManager->persist($image);
                        $count_main_images--;
                    }
                }
                $entityManager->flush();
            }
            return true;
        }
        return false;
    }

    /**
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Post $post, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        if ($post->getImages()) {
            $fs = new Filesystem();
            foreach ($post->getImages() as $image) {
                $path = $this->container->getParameter('kernel.root_dir') . '/../web/' . $image->getPath();
                $fs->remove($path);
            }
        }

        $this->addFlash('success', 'Post successfully removed');
        return $this->redirectToRoute('front_my_post_index');
    }

    /**
     * @Route("/{post_id}/image/{image_id}/delete", name="image_delete")
     */
    public function deleteImageAction($post_id, $image_id, Request $request)
    {
        $fs = new Filesystem();
        $entityManager = $this->getDoctrine()->getManager();
        $image = $entityManager->getRepository(Image::class)->find($image_id);
        $path = $this->container->getParameter('kernel.root_dir') . '/../web/' . $image->getPath();
        $fs->remove($path);

        $is_main_image = false;
        if ($image->getIsMain()) {
            $is_main_image = true;
        }
        $entityManager->remove($image);
        $entityManager->flush();

        if ($is_main_image) {
            $post = $entityManager->getRepository(Post::class)->find($post_id);
            if ($post && $post->getImages()) {
                $main_image = $post->getImages()->last();
                $main_image->setIsMain(1);
                $entityManager->persist($main_image);
                $entityManager->flush();
            }
        }
        $this->haveMainImage($post_id);

        $this->addFlash('success', 'Image successfully removed');
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/get_category_parameters_ajax", name="get_category_parameters_ajax")
     */
    public function ajaxGetParamsAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->request->has('category_id')) {
            $category = $this->getDoctrine()->getRepository(Category::class)->find($request->request->get('category_id'));
            $parameters = $this->getDoctrine()->getRepository(Parameter::class)->getByCategory($category);

            $result = [];
            if ($parameters) {
                foreach ($parameters as $parameter) {
                    $category_res = [
                        'id' => $category->getId(),
                        'name' => $category->getName(),
                        'parent' => $category->getParent() ? [
                            'id' => $category->getParent()->getId(),
                            'name' => $category->getParent()->getName(),
                        ] : null
                    ];

                    $options_res = [];
                    foreach ($parameter->getOptions() as $option) {
                        $options_res [] = [
                            'id' => $option->getId(),
                            'name' => $option->getName(),
                        ];
                    }
                    $parameter_res = [
                        'id' => $parameter->getId(),
                        'name' => $parameter->getName(),
                        'type' => $parameter->getType(),
                        'tag' => $parameter->getTag(),
                        'options' => $options_res
                    ];

                    $result []= [
                        'is_filter' => $parameter->getIsFilter(),
                        'is_column' => $parameter->getIsColumn(),
                        'is_post' => $parameter->getIsPost(),
                        'category' => $category_res,
                        'parameter' => $parameter_res,
                    ];
                }
            }

            return $this->json($result);
        }

        return $this->json([]);
    }

    /**
     * @Route("/get_categories_ajax", name="get_categories_ajax")
     */
    public function ajaxGetCategoriesAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->request->has('parent_category_id')) {
            $parent_category_id = $request->request->get('parent_category_id');
            $categories = $this->getDoctrine()->getRepository(Category::class)->findByParent($parent_category_id);

            $result = [];
            if ($categories) {
                foreach ($categories as $category) {
                    $sub_categories = $this->getDoctrine()->getRepository(Category::class)->findByParent($category->getId());
                    $sub_cats = [];
                    if ($sub_categories) {
                        foreach ($sub_categories as $sub_category) {
                            $sub_cats []= [
                                'id' => $sub_category->getId(),
                                'name' => $sub_category->getName(),
                            ];
                        }
                    }

                    $result []= [
                        'id' => $category->getId(),
                        'name' => $category->getName(),
                        'sub_categories' => $sub_cats,
                    ];
                }
            }

            return $this->json($result);
        }

        return $this->json([]);
    }


}
