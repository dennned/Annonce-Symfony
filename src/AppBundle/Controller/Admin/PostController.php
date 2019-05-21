<?php

namespace AppBundle\Controller\Admin;

//use AppBundle\Entity\Parameter;
use AppBundle\Entity\Image;
use AppBundle\Entity\OptionName;
use AppBundle\Entity\Parameter;
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
use Symfony\Component\Validator\Constraints\NotNull;


/**
 * @Route("/admin/posts", name="admin_post_")
 */

class PostController extends Controller
{
    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function indexAction(Request $request, PaginatorInterface $paginator)
    {
//        $option = $this->getDoctrine()->getRepository(OptionName::class)->findByName('0');
//
//        dump($this->haveMainImage(112));
//        die;



        $queryPosts = $this->getDoctrine()->getRepository(Post::class)->getQueryPosts();

        $pagination = $paginator->paginate(
            $queryPosts,
            $request->query->getInt('page', 1),
            20
        );

        $categoriesRepository = $this->getDoctrine()->getRepository(Category::class);
        return $this->render('admin/post/index.html.twig', [
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
        return $this->render('admin/post/show.html.twig', [
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
        $form = $this->createForm(PostType::class, $post);
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

            $this->addFlash('success', 'Post successfully updated');
            return $this->redirectToRoute('admin_post_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to update post');
        }

        return $this->render('admin/post/edit.html.twig', [
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
        return $this->redirectToRoute('admin_post_index');
    }


}
