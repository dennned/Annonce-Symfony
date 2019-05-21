<?php

namespace AppBundle\Controller\Front;

use AppBundle\Entity\Country;
use AppBundle\Form\AdminUserType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user", name="front_user_")
 */
class UserController extends Controller
{
//    /**
//     * @Route("", name="index", methods={"GET"})
//     */
//    public function indexAction(Request $request, PaginatorInterface $paginator)
//    {
//        $queryUsers = $this->getDoctrine()->getRepository(User::class)->getQueryUsers();
//
//        $pagination = $paginator->paginate(
//            $queryUsers ,
//            $request->query->getInt('page', 1),
//            20
//        );
//
//        return $this->render('admin/user/index.html.twig', [
//            'pagination' => $pagination,
//        ]);
//    }
//
//    /**
//     * @Route("/{id}", name="show", methods={"GET"})
//     */
//    public function showAction($id, User $user)
//    {
//        return $this->render('admin/user/show.html.twig', [
//            'user' => $user,
//        ]);
//    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function editAction($id, User $user, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();
        $countries = $entityManager->getRepository(Country::class)->findAll();
        $countries_res = [];
        foreach ($countries as $country) {
            $countries_res[$country->getId()] = $country->getPhoneCode();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

//            $email = $request->request->get('user')['email'];
//            $existing_user = $entityManager->getRepository(User::class)->findBy(['email' => $email]);
//            if ($existing_user) {
//                $this->addFlash('error', 'User with such email already exists!');
//                return $this->render('front/user/edit.html.twig', [
//                    'user' => $user,
//                    'form' => $form->createView(),
//                    'countries' => $countries_res,
//                ]);
//            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your profile successfully updated');
            return $this->redirectToRoute('front_index_index');
        }

        if ($form->getErrors()->count()) {
            $this->addFlash('error', 'Failed to update your profile');
        }
        return $this->render('front/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'countries' => $countries_res,
        ]);
    }

//    /**
//     * @Route("/{id}/delete", name="delete")
//     */
//    public function deleteAction(Request $request, User $user)
//    {
//        $entityManager = $this->getDoctrine()->getManager();
//        $entityManager->remove($user);
//        $entityManager->flush();
//
//        $this->addFlash('success', 'User successfully removed');
//        return $this->redirectToRoute('admin_user_index');
//    }

}
