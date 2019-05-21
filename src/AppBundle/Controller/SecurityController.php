<?php
namespace AppBundle\Controller;

use AppBundle\Entity\PasswordRecovery;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(AuthenticationUtils $authUtils)
    {
        // получить ошибку входа, если она есть
        $error = $authUtils->getLastAuthenticationError();
        // последнее имя пользователя, введенное пользователем
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/after_login", name="after_login")
     */
    public function afterLoginAction()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_post_index');
        }

        return $this->redirectToRoute('front_index_index');
    }

     /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route("/password_recovery/send", name="password_recovery_send")
     */
    public function sendPasswordRecoveryAction(Request $request)
    {
        if ($request->isMethod('post')) {
            $translator = $this->get('translator');
            $entityManager = $this->getDoctrine()->getManager();

            $email = $request->get('email');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $key = substr(md5(mt_rand()), 0, 32);
                $password_recovery = new PasswordRecovery();
                $password_recovery->setSecurityKey($key);
                $password_recovery->setUser($user);
                $entityManager->persist($password_recovery);
                $entityManager->flush();

                // send mail
                $url = $this->generateUrl('password_recovery_restore', ['key' => $key], UrlGeneratorInterface::ABSOLUTE_URL);
                $to = $user->getEmail();
                $subject = 'Password Recovery';
                $message = $translator->trans(
                    'security.send_password_recovery.mail_reset_password',
                    ['%url%' => $url]
                );
                mail($to, $subject, $message);

                $this->addFlash('success', $translator->trans('An email has been sent to your email with a link to reset your password'));
                return $this->redirectToRoute('front_index_index');
            }

            $this->addFlash('error', $translator->trans('User with this email not found'));
            return $this->redirectToRoute('front_index_index');
        }

        return $this->render('security/password_recovery/send.html.twig');
    }

    /**
     * @Route("/password_recovery/restore", name="password_recovery_restore")
     */
    public function restorePasswordRecoveryAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $translator = $this->get('translator');
        $entityManager = $this->getDoctrine()->getManager();

        $data = $request->request->all();
        if ($request->isMethod('post') && isset($data['user']) && $data['user']) {
            $user = $entityManager->getRepository(User::class)->find($data['user']['id']);

            if ($user && ($data['user']['plainPassword']['first'] === $data['user']['plainPassword']['second'])) {
                $password = $passwordEncoder->encodePassword($user, $data['user']['plainPassword']['first']);
                $user->setPassword($password);
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('Your password has been successfully changed'));
                return $this->redirectToRoute('login');
            }

            $this->addFlash('error', 'Failed to change password');
            return $this->redirectToRoute('login');
        }

        $key = $request->get('key');
        $password_recovery = $entityManager->getRepository(PasswordRecovery::class)->findOneBy(['securityKey' => $key]);

        if ($password_recovery) {
            $created_at = $password_recovery->getCreatedAt()->format('Y-m-d H:i:s');
            $link_destroy_time = strtotime('+2 hours', strtotime($created_at));
            $now_time = time();

            if ($now_time < $link_destroy_time) {
                $entityManager->remove($password_recovery);
                $entityManager->flush();

                return $this->render('security/password_recovery/restore.html.twig', [
                    'user_id' => $password_recovery->getUser()->getId()
                ]);
            }
        }

        $this->addFlash('error', 'Password recovery link lifetime out');
        return $this->redirectToRoute('front_index_index');
    }

}