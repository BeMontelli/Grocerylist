<?php

namespace App\Controller\Admin;

use App\Entity\GroceryList;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\GroceryListType;
use App\Form\IngredientType;
use App\Form\RecipeType;
use App\Repository\GroceryListRepository;
use App\Service\FileUploader;
use App\Service\GroceryListIngredientService;
use Doctrine\ORM\EntityManagerInterface;
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
#[IsGranted('ROLE_ADMIN')]
class AjaxController extends AbstractController
{
    public function __construct()
    {

    }

    #[Route('/element-toggle/', name: 'elementToggle', methods: ['POST'])]
    public function elementToggle(Request $request, Security $security): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();

        $data = json_decode($request->getContent(), true);
        $elementId = $data['elementId'] ?? null;

        if ($elementId === null) {
            return new JsonResponse(['error' => 'Ingredient ID not provided'], Response::HTTP_BAD_REQUEST);
        }
        
        return new JsonResponse(['success' => true, 'ingredientId' => $elementId]);
    }
}
