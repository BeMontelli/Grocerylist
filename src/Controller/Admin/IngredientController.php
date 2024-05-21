<?php

namespace App\Controller\Admin;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/{_locale}/admin/ingredients", name: "admin.ingredient.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class IngredientController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, IngredientRepository $ingredientRepository): Response
    {
        $currentPage = $request->query->getInt('page', 1);
        $ingredients = $ingredientRepository->paginateIngredients($currentPage);

        return $this->render('admin/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    #[Route('/create/', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class,$ingredient);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ingredient);
            $entityManager->flush();
            $this->addFlash('success', 'Ingredient saved !');
            return $this->redirectToRoute('admin.ingredient.index');
        }

        return $this->render('admin/ingredient/create.html.twig',[
            'form' => $form
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(string $slug, int $id, IngredientRepository $ingredientRepository): Response
    {
        return $this->render('admin/ingredient/show.html.twig', [
            'ingredient' => $ingredientRepository->find($id),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $entityManager->persist($formData);
            $entityManager->flush();
            $this->addFlash('success', 'Ingredient updated !');
            return $this->redirectToRoute('admin.ingredient.edit', ["id" => $ingredient->getId()]);
        }

        return $this->render('admin/ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Ingredient $ingredient, EntityManagerInterface $entityManager) {
        $entityManager->remove($ingredient);
        $entityManager->flush();
        $this->addFlash('success', 'Ingredient '.$ingredient->getTitle().' deleted !');
        return $this->redirectToRoute('admin.ingredient.index');
    }
}
