<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use AppBundle\Entity\OptionName;
use AppBundle\Entity\Parameter;
use AppBundle\Form\ParameterType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/parameters", name="admin_parameter_")
 */

class ParameterController extends Controller
{
    /**
     * @Route("", name="index")
     */
    public function indexAction(Request $request, PaginatorInterface $paginator)
    {

//        dump($paginator);
//        die;

        $catRep = $this->getDoctrine()->getRepository(Category::class);
        $paramRep = $this->getDoctrine()->getRepository(Parameter::class);

        $pagin_params = [];
        $section_id = 0;
        if ($request->isMethod('post') && $request->get('section_id')) {
            $section_id = $request->get('section_id');
            $section = $catRep->find($section_id);

            $pagin_params = $paramRep->getByCategory($section);
            $child_cats = $catRep->findAllChilds($section_id, true);
            if ($child_cats) {
                foreach ($child_cats as $child_cat) {
                    $child_params = $paramRep->getByCategorySimple($child_cat->getId());
                    $pagin_params = array_merge($pagin_params, $child_params);
                }
            }
        } else {
            $pagin_params = $paramRep->getQueryParams();
        }

        $pagination = $paginator->paginate(
            $pagin_params ? $pagin_params : [],
            $request->query->getInt('page', 1),
            20
        );
//        $pagination->setParam('section', $section_id);

        $locale = $request->getLocale();
        $field_name = 'name'.ucfirst($locale);
        $categories = $catRep->findBy([], [$field_name => 'ASC']);
        return $this->render('admin/parameter/index.html.twig', [
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
        $parameter = new Parameter();
        $form = $this->createForm(ParameterType::class, $parameter);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all();
            if (isset($data['parameter']['options']) && count($data['parameter']['options']) > 0) {
                foreach ($data['parameter']['options'] as $option) {
                    $new_option = new OptionName();
                    $new_option->setName($data['parameter']['type'], $option);
                    $parameter->addOption($new_option);
                }
            }

            $entityManager->persist($parameter);
            $entityManager->flush();

            $this->addFlash('success', 'Parameter successfully added');
            return $this->redirectToRoute('admin_parameter_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to add parameter');
        }
        return $this->render('admin/parameter/new.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show")
     */
    public function showAction(Parameter $parameter)
    {
        return $this->render('admin/parameter/show.html.twig', [
            'parameter' => $parameter,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit")
     */
    public function editAction(Parameter $parameter, Request $request)
    {
        $form = $this->createForm(ParameterType::class, $parameter);
        $form->handleRequest($request);
        $data = $request->request->all();
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $data_parameter = $data['parameter'];
            if (isset($data_parameter['options']) && count($data_parameter['options']) > 0) {
                foreach ($data_parameter['options'] as $option) {
                    $new_option = new OptionName();
                    $new_option->setName($data_parameter['type'], $option);
                    $parameter->addOption($new_option);
                }
            }

            $entityManager->persist($parameter);
            $entityManager->flush();

            $this->addFlash('success', 'Parameter successfully updated');
            return $this->redirectToRoute('admin_parameter_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to update parameter');
        }
        return $this->render('admin/parameter/edit.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
            'parameter' => $parameter,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Parameter $parameter)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($parameter);
        $entityManager->flush();

        $this->addFlash('success', 'Parameter successfully removed');
        return $this->redirectToRoute('admin_parameter_index');
    }

}
