<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/recipes/{slug}-{id}', name: 'recipe.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id): Response
    {
        return $this->render('recipe/show.html.twig', [
            'id' => $id,
            'slug' => $slug,
            'content' => "<strong>test</strong> and <u>test</u>",
            'arr' => [
                "test","testest"
            ],
            'arrmult' => [
                ["name" => "test"],
                ["name" => "test2"],
            ],
            'controller_name' => 'RecipeController',
        ]);
    }
}
