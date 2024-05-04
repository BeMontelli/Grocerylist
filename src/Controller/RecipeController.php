<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipes/', name: 'recipe.index')]
    public function index(Request $request): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    #[Route('/recipes/{slug}-{id}', name: 'recipe.show')]
    public function show(Request $request): Response
    {
        return $this->render('recipe/show.html.twig', [
            'id' => $request->attributes->get('id'),
            'slug' => $request->attributes->get('slug'),
            'controller_name' => 'RecipeController',
        ]);
    }
}
