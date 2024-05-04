<?php

namespace App\Controller;

use App\Entity\Recipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecipeController extends AbstractController
{
    #[Route('/recipes/', name: 'recipe.index')]
    public function index(Request $request, RecipeRepository $recipeRepository,EntityManagerInterface $entityManager): Response
    {
        /*$recipe = new Recipe();
        $recipe->setTitle("Burger")
            ->setSlug("burger")
            ->setPrice(5.80)
            ->setContent("<b>test</b> fsegrd")
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($recipe);
        $entityManager->flush();*/

        /*$entityManager->remove($recipe);*/

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipeRepository->findAll()
        ]);
    }

    #[Route('/recipes/{slug}-{id}', name: 'recipe.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, RecipeRepository $recipeRepository): Response
    {
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipeRepository->find($id),
        ]);
    }
}
