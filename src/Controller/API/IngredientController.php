<?php

namespace App\Controller\API;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
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

#[Route("/api/v1/ingredients", name: "api.ingredient.")]
class IngredientController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, IngredientRepository $ingredientRepository, SerializerInterface $serializer): Response
    {
        $ingredients = $ingredientRepository->findAll();
        return $this->json($ingredients,Response::HTTP_OK, [], [
            'groups' => ['ingredients.*','ingredients.index']
        ]);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/ingredient/create.html.twig',[
            '' => ''
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(int $id, IngredientRepository $ingredientRepository): Response
    {
        return $this->json($ingredientRepository->find($id),Response::HTTP_OK, [], [
            'groups' => ['ingredients.*','ingredients.show']
        ]);
    }

    #[Route('/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    public function edit(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/ingredient/edit.html.twig',[
            '' => ''
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Ingredient $ingredient, EntityManagerInterface $entityManager) {
        $entityManager->remove($ingredient);
        $entityManager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
