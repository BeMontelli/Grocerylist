<?php

namespace App\Controller\Admin;

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

#[Route("/admin/ajax", name: "admin.ajax.")]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class AjaxController extends AbstractController
{
    public function __construct()
    {

    }

    #[Route('/ingredient-toggle/', name: 'ingredientToggle', methods: ['POST'])]
    public function ingredientToggle(Request $request, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();

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

    #[Route('/search-by-title/', name: 'searchTitle', methods: ['POST'])]
    public function searchByTitle(Request $request, Security $security, SearchRepository $searchRepository): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();

        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? null;

        if ($title === null) {
            return new JsonResponse(['error' => 'Title to search not provided'], Response::HTTP_BAD_REQUEST);
        }

        $results = $searchRepository->search($title,$user->getId());

        return new JsonResponse(['success' => true, 'results' => $results], Response::HTTP_OK);
    }

    #[Route('/sections-position/', name: 'sectionsPosition', methods: ['POST'])]
    public function updateSectionsPosition(Request $request, Security $security, SectionRepository $sectionRepository): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();

        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? null;

        if ($ids === null || empty($ids)) {
            return new JsonResponse(['error' => 'Ids to update not provided'], Response::HTTP_BAD_REQUEST);
        }

        $sectionRepository->updatePositions($ids,$user);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    #[Route('/categories-position/', name: 'categoriesPosition', methods: ['POST'])]
    public function updateCategoriesPosition(Request $request, Security $security, CategoryRepository $categoryRepository): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();

        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? null;

        if ($ids === null || empty($ids)) {
            return new JsonResponse(['error' => 'Ids to update not provided'], Response::HTTP_BAD_REQUEST);
        }

        $categoryRepository->updatePositions($ids,$user);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
