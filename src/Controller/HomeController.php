<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'page.home')]
    public function index(Request $request): Response
    {
        return $this->render('home/index.html.twig', [
            'test' => $request->query->get('test'),
            'controller_name' => 'HomeController',
        ]);
    }
}
