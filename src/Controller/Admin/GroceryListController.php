<?php

namespace App\Controller\Admin;

use App\Entity\GroceryList;
use App\Entity\Ingredient;
use App\Entity\GroceryListIngredient;
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

#[Route("/{_locale}/admin/lists", name: "admin.list.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class GroceryListController extends AbstractController
{
    private $security;
    private $fileUploader;
    private $groceryListIngredientService;

    public function __construct(Security $security, FileUploader $fileUploader, GroceryListIngredientService $groceryListIngredientService)
    {
        $this->security = $security;
        $this->fileUploader = $fileUploader;
        $this->groceryListIngredientService = $groceryListIngredientService;
    }

    #[Route('/', name: 'index', methods: ['GET','POST'])]
    public function index(Request $request, GroceryListRepository $groceryListRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);
        $lists = $groceryListRepository->paginateUserLists($currentPage,$user);

        $groceryList = new GroceryList();
        $form = $this->createForm(GroceryListType::class, $groceryList);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $groceryList->setUser($user);
                $entityManager->persist($groceryList);
                $entityManager->flush();
                $this->addFlash('success', 'List saved !');
                return $this->redirectToRoute('admin.list.index');
            } else $this->addFlash('danger', 'Form validation error !');
        }

        return $this->render('admin/grocery_list/index.html.twig', [
            'grocery_lists' => $lists,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(string $slug, int $id,GroceryListRepository $groceryListRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var GroceryList $groceryList  */
        $groceryList = $groceryListRepository->find($id);
        // fully load object
        $entityManager->refresh($groceryList);
        
        /** @var User $user  */
        $user = $this->security->getUser();
        $user->setCurrentGroceryList($groceryList);
        $entityManager->flush();

        $groceryListIngredients = $groceryList->getGroceryListIngredients();
        // collection objects fully initialize if not
        foreach ($groceryListIngredients as $groceryListIngredient) {
            $ingredient = $groceryListIngredient->getIngredient();
            if ($ingredient instanceof Proxy) {
                $entityManager->initializeObject($ingredient);
            }
        }

        $recipes = $groceryList->getRecipes();
        // collection objects fully initialize if not
        foreach ($recipes as $recipe) {
            $recipeGroceryListIngredients = $recipe->getGroceryListIngredients();
            foreach ($recipeGroceryListIngredients as $groceryListIngredient) {
                if ($recipeGroceryListIngredients instanceof Proxy) {
                    $entityManager->initializeObject($recipeGroceryListIngredients);
                }
            }
        }

        return $this->render('admin/grocery_list/show.html.twig', [
            'grocery_list' => $groceryList,
            'elements' => $this->groceryListIngredientService->getIngredientsStructured($groceryListIngredients->toArray()),
            'recipes' => $recipes,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, GroceryList $groceryList, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $form = $this->createForm(GroceryListType::class, $groceryList);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'List updated !');
                return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
            } else $this->addFlash('danger', 'Form validation error !');
        }

        return $this->render('admin/grocery_list/edit.html.twig', [
            'grocery_list' => $groceryList,
            'form' => $form
        ]);
    }

    #[Route('/remove-recipe-list/{grocerylistId}/{recipeId}', name: 'removeRecipeList', requirements: ['grocerylistId' => Requirement::DIGITS,'recipeId' => Requirement::DIGITS,], methods: ['GET'])]
    public function removeRecipeFromList(int $grocerylistId,int $recipeId,Request $request,EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var GroceryList $groceryList  */
        $groceryList = $em->find(GroceryList::class,$grocerylistId);
        /** @var Recipe $recipe  */
        $recipe = $em->find(Recipe::class,$recipeId);

        if(!$groceryList || !$recipe) {
            return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
        }

        if($groceryList->getUser()->getId() === $user->getId()) {
            $groceryList->removeRecipe($recipe);
            $em->persist($groceryList);
            $em->flush();
            $this->groceryListIngredientService->removeRecipeIngredientsInGroceryList($recipe,$groceryList);
            return $this->redirectToRoute('admin.list.show', ['slug'=> $groceryList->getSlug(),'id'=> $groceryList->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/toggle-ingredient-list/{grocerylistId}/{ingredientId}/{inlist}', name: 'toggleIngredientList', requirements: ['grocerylistId' => Requirement::DIGITS,'ingredientId' => Requirement::DIGITS,'inlist' => 'remove|put'], methods: ['GET'])]
    public function toggleIngredientFromList(int $grocerylistId,int $ingredientId,string $inlist,Request $request,EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var GroceryList $groceryList  */
        $groceryList = $em->find(GroceryList::class,$grocerylistId);
        /** @var Ingredient $ingredient  */
        $ingredient = $em->find(Ingredient::class,$ingredientId);

        if(!$groceryList || !$ingredient) {
            return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
        }

        if($groceryList->getUser()->getId() === $user->getId()) {

            $inListTarget = !($inlist === 'remove');

            $groceryListIngredients = $em->getRepository(GroceryListIngredient::class)
            ->findBy([
                'groceryList'=> $groceryList->getId(),
                'ingredient'=> $ingredient->getId()
            ]);

            foreach ($groceryListIngredients as $groceryListIngredient) {
                $groceryListIngredient->setInList($inListTarget);
                $groceryListIngredient->setActivation(false);
                $em->persist($groceryListIngredient);
            }

            $em->flush();

            return $this->redirectToRoute('admin.list.show', ['slug'=> $groceryList->getSlug(),'id'=> $groceryList->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/toggle-grocerylistingredient-list/{grocerylistId}/{grocerylistingredientId}/{inlist}', name: 'toggleGroceryListIngredientList', requirements: ['grocerylistId' => Requirement::DIGITS,'grocerylistingredientId' => Requirement::DIGITS,'inlist' => 'remove|put'], methods: ['GET'])]
    public function toggleGroceryListIngredientFromList(int $grocerylistId,int $grocerylistingredientId,string $inlist,Request $request,EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var GroceryList $groceryList  */
        $groceryList = $em->find(GroceryList::class,$grocerylistId);
        /** @var GroceryListIngredient $grocerylistingredientId  */
        $groceryListIngredient = $em->find(GroceryListIngredient::class,$grocerylistingredientId);

        if(!$groceryList || !$groceryListIngredient) {
            return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
        }

        if($groceryList->getUser()->getId() === $user->getId()) {

            $inListTarget = !($inlist === 'remove');

            $groceryListIngredient->setInList($inListTarget);
            $groceryListIngredient->setActivation(false);
            $em->persist($groceryListIngredient);

            $em->flush();

            return $this->redirectToRoute('admin.list.show', ['slug'=> $groceryList->getSlug(),'id'=> $groceryList->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    public function delete(Request $request, GroceryList $groceryList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groceryList->getId(), $request->getPayload()->get('_token'))) {

            $usersWithGroceryList = $entityManager->getRepository(User::class)->findBy(['current_grocery_list' => $groceryList]);
            foreach ($usersWithGroceryList as $user) {
                $user->setCurrentGroceryList(null);
            }

            $entityManager->remove($groceryList);
            $entityManager->flush();
            $this->addFlash('warning', 'List '.$groceryList->getTitle().' deleted !');
        } else {
            $this->addFlash('danger', 'Error occured !');
        }

        return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
    }
}
