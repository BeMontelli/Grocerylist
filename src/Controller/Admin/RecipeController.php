<?php

namespace App\Controller\Admin;

use App\DTO\SearchRecipesDTO;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\RecipeType;
use App\Entity\GroceryList;
use App\Repository\RecipeRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Form\GroceryListRecipeIngredientsType;
use App\Repository\GroceryListRepository;
use App\Service\GroceryListIngredientService;
use App\Form\SearchRecipesType;

#[Route("/{_locale}/admin/recipes", name: "admin.recipe.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class RecipeController extends AbstractController
{
    private $security;
    private $fileUploader;
    private $groceryListIngredientService;

    public function __construct(Security $security, FileUploader $fileUploader,GroceryListIngredientService $groceryListIngredientService)
    {
        $this->security = $security;
        $this->fileUploader = $fileUploader;
        $this->groceryListIngredientService = $groceryListIngredientService;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request, RecipeRepository $recipeRepository, EntityManagerInterface $em): Response
    {
        /** @var User $user  */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);

        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class,$recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('thumbnailfile')->getData();
            if ($file) $thumbnailPath = $this->fileUploader->uploadRecipeThumbnail($file);

            /** @var Recipe $recipe */
            $recipe = $form->getData();
            if(!empty($thumbnailPath)) $recipe->setThumbnail($thumbnailPath);

            $recipe = $form->getData();
            $recipe->setUser($user);
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Recipe saved !');
            return $this->redirectToRoute('admin.recipe.index');
        }

        $search = new SearchRecipesDTO();
        $formSearch = $this->createForm(SearchRecipesType::class,$search, [
            'attr' => ['data-turbo' => 'false']
        ]);
        $formSearch->handleRequest($request);
        if($formSearch->isSubmitted() && $formSearch->isValid()) {
            $title = $formSearch->get('title')->getData();
            $categories = $formSearch->get('categories')->getData();
            $recipes = $recipeRepository->paginateUserSearchedRecipes($currentPage,$user,$title,$categories);
        } else {
            $recipes = $recipeRepository->paginateUserRecipes($currentPage,$user);
        }
        
        return $this->render('admin/recipe/index.html.twig', [
            'recipes' => $recipes,
            'form' => $form,
            'formSearch' => $formSearch
        ]);

    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG], methods: ['GET','POST'])]
    public function show(string $slug, int $id, Request $request, EntityManagerInterface $em, RecipeRepository $recipeRepository): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $currentGrocerylistId = ($user->getCurrentGroceryList() && $user->getCurrentGroceryList()->getId()) ? $user->getCurrentGroceryList()->getId(): null;

        $recipe = $recipeRepository->findRecipeWithCategory($id);
        $allIngredients = $recipe->getIngredients();

        $groceryLists = $user->getGroceryLists();
        $choices = [];
        foreach ($groceryLists as $groceryList) {
            $choices[$groceryList->getTitle()] = $groceryList->getId();
        }

        // FORM add recipe to groceryList with ingredients
        $formlist = $this->createForm(GroceryListRecipeIngredientsType::class,[
            'recipe' => $recipe,
            'ingredients' => $allIngredients->toArray(),
            'choices' => $choices,
            'currentGrocerylistId' => $currentGrocerylistId,
        ]);
        $formlist->handleRequest($request);
        if ($formlist->isSubmitted() && $formlist->isValid()) {
            $groceryListId = $formlist->get('groceryList')->getData();
            $ingredients = $formlist->get('ingredients')->getData();
            $remainingIngredients = array_filter($allIngredients->toArray(), function ($ingredient) use ($ingredients) {
                foreach ($ingredients as $selectedIngredient) {
                    if ($ingredient->getId() === $selectedIngredient->getId()) {
                        return false;
                    }
                }
                return true;
            });

            $groceryList = $em->getRepository(GroceryList::class)->find($groceryListId);
            if (!$groceryList) {
                $this->addFlash('danger', 'GroceryList do not exists.');
                return $this->redirectToRoute('admin.recipe.show', ['id' => $recipe->getId(),'slug' => $recipe->getSlug()]);
            }

            $groceryList->addRecipe($recipe);
            $em->persist($groceryList);
            
            $this->groceryListIngredientService->removeRecipeIngredientsInGroceryList($recipe,$groceryList);
            foreach ($ingredients as $ingredient) {
                $this->groceryListIngredientService->setGroceryListIngredient(
                    $ingredient,
                    $recipe,
                    $groceryList,
                    false,
                    true
                );
            }
            foreach ($remainingIngredients as $ingredient) {
                $this->groceryListIngredientService->setGroceryListIngredient(
                    $ingredient,
                    $recipe,
                    $groceryList,
                    false,
                    false
                );
            }

            $em->flush();
            $this->addFlash('success', 'Recipe added to list !');

            return $this->redirectToRoute('admin.recipe.show', ['id' => $recipe->getId(),'slug' => $recipe->getSlug()]);
        }

        // FORM edit recipe
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            /** @var Recipe $recipe */
            $recipe = $form->getData();
            $oldThumbnail = $recipe->getThumbnail();

            $file = $form->get('thumbnailfile')->getData();
            if ($file) $thumbnailPath = $this->fileUploader->uploadRecipeThumbnail($file);

            if(!empty($thumbnailPath)) {
                $fileDir = $this->getParameter('kernel.project_dir').'/public';
                $this->fileUploader->deleteThumbnail($fileDir,$oldThumbnail);
                /** @var Recipe $recipe */
                $recipe->setThumbnail($thumbnailPath);
            }

            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Recipe updated !');
            return $this->redirectToRoute('admin.recipe.show', ['id' => $recipe->getId(),'slug' => $recipe->getSlug()]);
        }

        return $this->render('admin/recipe/show.html.twig', [
            'formlist' => $formlist->createView(),
            'form' => $form,
            'recipe' => $recipe
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Request $request,Recipe $recipe, EntityManagerInterface $em) {

        if ($this->isCsrfTokenValid('delete'.$recipe->getId(), $request->getPayload()->get('_token'))) {
            $currentThumbnail = $recipe->getThumbnail();
            if(!empty($currentThumbnail)) {
                $fileDir = $this->getParameter('kernel.project_dir').'/public';
                $this->fileUploader->deleteThumbnail($fileDir,$currentThumbnail);
            }
    
            $em->remove($recipe);
            $em->flush();
            $this->addFlash('warning', 'Recipe '.$recipe->getTitle().' deleted !');
        } else {
            $this->addFlash('danger', 'Error occured !');
        }

        return $this->redirectToRoute('admin.recipe.index');
    }
}
