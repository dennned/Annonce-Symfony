<?php 

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use Locale;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Route("/admin", name="admin_")
 */
class IndexController extends Controller
{
	/**
	 * @Route("", name="index")
	 */
	public function indexAction()
    {
        $catRepos = $this->getDoctrine()->getRepository(Category::class);
        $root_categories = $catRepos->findByParent(0);

        $categories = [];
        foreach ($root_categories as $root_category) {
            if ($root_category->getName() == 'root') {
                continue;
            }
            $categories[$root_category->getName()] = $catRepos->findByParent($root_category->getId());
        }

//        echo '<pre>';
//        dump($categories);
//        die;

		return $this->render('admin/index/index.html.twig', [
            'categories' => $categories,
        ]);
	}

    /**
     * @Route("/testtt", name="testtt")
     */
    public function testttAction(Swift_Mailer $mailer)
    {
//        $translator = $this->get('translator');
//        $message = $translator->trans(
//            'password_recovery.mail_reset_password',
//            ['%url%' => 'dscsdcsdcsd']
//        );

        var_dump($this->container->get('kernel')->getEnvironment());
        die;

//        dump(ucfirst($GLOBALS['request']->getLocale()));
//        die;

//         Create a message
//        $message = (new Swift_Message('Test mail'))
//            ->setFrom(['john@doe.com' => 'John Doe'])
//            ->setTo('enromanenko86@gmail.com')
//            ->setBody('Test mail');
//
//        $result = $mailer->send($message);
//        dump($result);
//        die;
//
//        $to      = 'enromanenko86@gmail.com';
//        $subject = 'the subject';
//        $message = 'hello';
////        $headers = 'From: webmaster@example.com' . "\r\n" .
////            'Reply-To: webmaster@example.com' . "\r\n" .
////            'X-Mailer: PHP/' . phpversion();
//
//        mail($to, $subject, $message);
//        die('okiii');
    }






}