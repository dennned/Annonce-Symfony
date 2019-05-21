<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\OptionName;
use AppBundle\Entity\Parameter;
use AppBundle\Form\ParameterType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/options", name="admin_option_")
 */

class OptionController extends Controller
{

    /**
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, OptionName $option)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($option);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/{id}/delete_ajax", name="delete_ajax")
     */
    public function ajaxDeleteAction(Request $request, OptionName $option)
    {
        if ($request->isXmlHttpRequest()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($option);
            $entityManager->flush();

            return $this->json('success');
        }

        return $this->json('error');
    }

}
