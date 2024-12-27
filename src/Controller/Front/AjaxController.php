<?php

namespace App\Controller\Front;

use App\Entity\GroceryList;
use App\Entity\GroceryListIngredient;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\GroceryListType;
use App\Form\IngredientType;
use App\Repository\CategoryRepository;
use App\Repository\GroceryListRepository;
use App\Repository\SearchRepository;
use App\Repository\SectionRepository;
use App\Service\GroceryListIngredientService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\Persistence\Proxy;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route("/front/ajax", name: "front.ajax.")]
class AjaxController extends AbstractController
{
    public function __construct()
    {

    }

    #[Route('/ingredient-toggle/', name: 'ingredientToggle', methods: ['POST'])]
    public function ingredientToggle(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ingredientId = $data['ingredientId'] ?? null;
        $listId = $data['listId'] ?? null;

        if ($ingredientId === null) {
            return new JsonResponse(['error' => 'Ingredient ID not provided'], Response::HTTP_BAD_REQUEST);
        }

        if ($listId === null) {
            return new JsonResponse(['error' => 'GroceryList ID not provided'], Response::HTTP_BAD_REQUEST);
        }

        $groceryList = $entityManager->getRepository(GroceryList::class)->find($listId);
        
        if($groceryList === null || $groceryList->getPublicSlug() === null) {
            return new JsonResponse(['error' => 'GroceryList not found'], Response::HTTP_NOT_FOUND);
        }

        $groceryList->setUpdatedAt(updatedAt: new \DateTimeImmutable());
        $entityManager->persist($groceryList);

        $groceryListIngredients = $entityManager->getRepository(GroceryListIngredient::class)->findBy(['ingredient' => $ingredientId,'groceryList' => $listId]);
        foreach ($groceryListIngredients as $groceryListIngredient) {
            $status = $groceryListIngredient->isActive();
            $groceryListIngredient->setActivation(!$status);
            $entityManager->persist($groceryListIngredient);
        }
        $entityManager->flush();
        
        return new JsonResponse(['success' => true, 'ingredientId' => $ingredientId, 'listId' => $listId]);
    }
}
