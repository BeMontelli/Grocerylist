<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin", name: "admin.")]
#[IsGranted('ROLE_USER')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(Request $request): Response
    {
        return $this->render('admin/pages/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
