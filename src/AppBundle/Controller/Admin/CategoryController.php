<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Image;
use AppBundle\Entity\Parameter;
use AppBundle\Form\CategoryType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Category;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 * @Route("/admin/categories", name="admin_category_")
 */
class CategoryController extends Controller
{
    /**
     * @Route("", name="index")
     */
    public function indexAction(PaginatorInterface $paginator, Request $request)
    {
        $catRep = $this->getDoctrine()->getRepository(Category::class);

        $pagin_categories = [];
        $section_id = 0;
        if ($request->isMethod('post') && $request->get('section_id')) {
            $section_id = $request->get('section_id');
            $section = $catRep->find($section_id);
            $categories = $catRep->findAllChilds($section_id, true);
            $pagin_categories = array_merge([$section], $categories ? $categories : []);
        } else {
            $pagin_categories = $catRep->getQueryCategories();
        }

        $pagination = $paginator->paginate(
            $pagin_categories ? $pagin_categories : [],
            $request->query->getInt('page', 1),
            20
        );

        $locale = $request->getLocale();
        $field_name = 'name'.ucfirst($locale);
        $categories = $catRep->findBy([], [$field_name => 'ASC']);
        return $this->render('admin/category/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
            'section_id' => $section_id,
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function newAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $files_manager = $this->get('oneup_uploader.orphanage_manager')->get('gallery');
            $files = $files_manager->uploadFiles();
            if (isset($files[0])) {
                $file = $files[0];
                $image = new Image();
                $image->setName($file->getFilename());
                $image->setPath('uploads/gallery/' . $file->getFilename());
                $image->setIsMain(1);
                $entityManager->persist($image);
                $category->setImage($image);
            }

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category successfully added');
            return $this->redirectToRoute('admin_category_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to add category');
        }
        return $this->render('admin/category/new.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction($id, Category $category)
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function editAction($id, Request $request, Category $category)
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$category->getImage()) {
                $files_manager = $this->get('oneup_uploader.orphanage_manager')->get('gallery');
                $files = $files_manager->uploadFiles();
                if (isset($files[0])) {
                    $file = $files[0];
                    $image = new Image();
                    $image->setName($file->getFilename());
                    $image->setPath('uploads/gallery/' . $file->getFilename());
                    $image->setIsMain(1);
                    $entityManager->persist($image);
                    $category->setImage($image);
                }
            }

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category successfully updated');
            return $this->redirectToRoute('admin_category_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to update category');
        }
        return $this->render('admin/category/edit.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
            'current_category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'Category successfully removed');
        return $this->redirectToRoute('admin_category_index');
    }

    /**
     * @Route("/{category_id}/image/{image_id}/delete", name="image_delete")
     */
    public function deleteImageAction($category_id, $image_id, Request $request)
    {
        $fs = new Filesystem();
        $image = $this->getDoctrine()->getRepository(Image::class)->find($image_id);
        $path = $this->container->getParameter('kernel.root_dir') . '/../web/' . $image->getPath();
        $fs->remove($path);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($image);
        $entityManager->flush();

        $this->addFlash('success', 'Image successfully removed');
        return $this->redirectToRoute('admin_category_edit', ['id' => $category_id]);
    }

}
