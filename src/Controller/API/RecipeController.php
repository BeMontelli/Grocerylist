<?php

namespace App\Controller\API;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/v1/recipes", name: "api.recipe.")]
class RecipeController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, RecipeRepository $recipeRepository, SerializerInterface $serializer): Response
    {
        $currentPage = $request->query->getInt('page', 1);
        $recipes = $recipeRepository->paginateRecipesWithCategories($currentPage);
        return $this->json($recipes,Response::HTTP_OK, [], [
            'groups' => ['recipes.index']
        ]);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/recipe/create.html.twig',[
            '' => ''
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(int $id, RecipeRepository $recipeRepository): Response
    {
        return $this->json($recipeRepository->find($id),Response::HTTP_OK, [], [
            'groups' => ['recipes.show']
        ]);
    }

    #[Route('/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/recipe/edit.html.twig',[
            '' => ''
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Recipe $recipe, EntityManagerInterface $entityManager) {
        $currentThumbnail = $recipe->getThumbnail();
        $fileDir = $this->getParameter('kernel.project_dir').'/public';
        if(!empty($currentThumbnail)) unlink($fileDir.$currentThumbnail);

        $entityManager->remove($recipe);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
