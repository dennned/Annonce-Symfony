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


class ExceptionController extends Controller
{

	/**
     * @Route("/exception", name="exception")
     */
	public function showExceptionAction(Request $request)
    {
        $path = $request->getPathInfo();
        if (strpos($path, '/fr/') !== false) {
            return $this->render('exception/error_fr.html.twig');
        }

        return $this->render('exception/error_en.html.twig');
    }
	

}
