<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: "page.root")]
    public function root(Request $request): Response
    {
        $preferredLanguage = $request->getPreferredLanguage(['en', 'fr']);

        if ($preferredLanguage === 'fr') return $this->redirectToRoute('page.home', ["_locale" => $preferredLanguage]);

        return $this->redirectToRoute('page.home', ["_locale" => 'en']);
    }

    #[Route("/{_locale}/", name: "page.home", requirements: ['_locale' => 'fr|en'])]
    public function index(Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        if($user) {
            return $this->redirectToRoute('admin.dashboard');
        } else {
            return $this->render('front/pages/index.html.twig');
        }

    }
}
