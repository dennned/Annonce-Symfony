<?php 

namespace AppBundle\Controller;

use AppBundle\Entity\Country;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegistrationController extends Controller
{

	/**
     * @Route("/register", name="register")
     */
	public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository(Country::class)->findAll();
        $countries_res = [];
        foreach ($countries as $country) {
            $countries_res[$country->getId()] = $country->getPhoneCode();
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->has('accept_rules')) {
                $translator = $this->get('translator');

                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                $email = $request->request->get('user')['email'];
                $existing_user = $em->getRepository(User::class)->findBy(['email' => $email]);
                if ($existing_user) {
                    $this->addFlash('error', $translator->trans('password_recovery_mail_mess'));
                    return $this->render('registration/register.html.twig', [
                        'form' => $form->createView(),
                        'countries' => $countries_res,
                    ]);
                }

                $em->persist($user);
                $em->flush();

                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));

                $this->addFlash('success', $translator->trans('Success! Thank you for your registration. Best Regards'));
                return $this->redirectToRoute('front_index_index');
            }
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
            'countries' => $countries_res,
        ]);
    }
	

}
