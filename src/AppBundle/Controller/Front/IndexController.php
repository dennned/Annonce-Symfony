<?php 

namespace AppBundle\Controller\Front;

use AppBundle\Entity\Category;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Route("", name="front_index_")
 */
class IndexController extends Controller
{
	/**
	 * @Route("/", name="index")
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
            $categories[$root_category->getName()] = [
                'sub_categories' => $catRepos->findByParent($root_category->getId()),
                'image_path' => $root_category->getImage() ? $root_category->getImage()->getPath() : '',
            ];
        }

		return $this->render('front/index/index.html.twig', [
            'categories' => $categories,
        ]);
	}

    /**
     * @Route("/rules", name="rules")
     */
    public function rulesAction()
    {
        return $this->render('front/index/rules.html.twig');
    }

    /**
     * @Route("/questions", name="questions")
     */
    public function questionsAction()
    {
        return $this->render('front/index/questions.html.twig');
    }

    /**
     * @Route("/ads", name="ads")
     */
    public function adsAction()
    {
        return $this->render('front/index/ads.html.twig');
    }

    /**
     * @Route("/cooperation", name="cooperation")
     */
    public function cooperationAction()
    {
        return $this->render('front/index/cooperation.html.twig');
    }

    /**
     * @Route("/captcha_ajax", name="captcha_ajax", methods={"POST"})
     */
    public function ajaxCaptchaAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->request->has('g-recaptcha-response')) {

            $recaptcha_response = $request->request->get('g-recaptcha-response');
//            $secret = '6Le4ip4UAAAAADmq7nSITFCfQeZgoS29dYOHv7K0';
            $secret = '6LduBaMUAAAAANNjLQlcIquk4IU2W-LbAlWOTIJg';
            $recaptcha = new ReCaptcha($secret);
            $remote_ip = $request->getClientIp();
            $resp = $recaptcha->verify($recaptcha_response, $remote_ip);

            return $this->json($resp->isSuccess());
        }

        return $this->json(false);
    }




}
