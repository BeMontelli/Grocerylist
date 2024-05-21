<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/{_locale}", name: "page.", requirements: ['_locale' => 'fr|en'])]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        return $this->render('front/pages/index.html.twig', [
            'test' => $request->query->get('test'),
            'controller_name' => 'HomeController',
        ]);
    }
}
