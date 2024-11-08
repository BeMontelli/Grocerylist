<?php

namespace App\Controller\Admin;

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

#[Route("/{_locale}/admin/recipes", name: "admin.recipe.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class RecipeController extends AbstractController
{
    private $security;
    private $fileUploader;

    public function __construct(Security $security, FileUploader $fileUploader)
    {
        $this->security = $security;
        $this->fileUploader = $fileUploader;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request, RecipeRepository $recipeRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user  */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);
        $recipes = $recipeRepository->paginateUserRecipes($currentPage,$user);

        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class,$recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('thumbnailfile')->getData();
            if ($file) $thumbnailPath = $this->fileUploader->uploadRecipeThumbnail($file);

            /** @var Recipe $recipe */
            $recipe = $form->getData();
            if($thumbnailPath) $recipe->setThumbnail($thumbnailPath);

            $recipe = $form->getData();
            $recipe->setUser($user);
            $entityManager->persist($recipe);
            $entityManager->flush();
            $this->addFlash('success', 'Recipe saved !');
            return $this->redirectToRoute('admin.recipe.index');
        }

        return $this->render('admin/recipe/index.html.twig', [
            'recipes' => $recipes,
            'form' => $form
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG], methods: ['GET','POST'])]
    public function show(string $slug, int $id, Request $request, EntityManagerInterface $em, RecipeRepository $recipeRepository, EntityManagerInterface $entityManager): Response
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

        $formlist = $this->createForm(GroceryListRecipeIngredientsType::class,[
            'recipe' => $recipe,
            'ingredients' => $allIngredients->toArray(),
            'choices' => $choices,
            'currentGrocerylistId' => $currentGrocerylistId,
        ]);

        $formlist->handleRequest($request);
        if ($formlist->isSubmitted() && $formlist->isValid()) {
            $groceryList = $formlist->get('groceryList')->getData();
            $ingredients = $formlist->get('ingredients')->getData();

            //foreach ($ingredients as $ingredient) {
            //    $groceryList->addIngredient($ingredient);
            //}

            // WIP
            // add recipe to groceryList
            // // check if already linked ?
            // // // if yes do nothing
            // // // if not default system process (add link)
            // add ingredients to groceryList
            // // get all recipe ingredients
            // // compare ingredients list from form
            // // check if $ingredients in $allIngredients && selected in form
            // // // if yes create array groceryListIngredients with inList true
            // // // if no create array groceryListIngredients with inList false
            // // delete groceryListIngredients in list with recipe
            // // rebuild groceryListIngredients with list and recipe

            dump($recipe);
            dump($groceryList);
            dump($ingredients->toArray());
            dump($allIngredients->toArray());
            dd($formlist);

            //$em->persist($groceryList);
            //$em->flush();

            return $this->redirectToRoute('admin.recipe.show', ['id' => $recipe->getId(),'slug' => $recipe->getSlug()]);
        }

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            /** @var Recipe $recipe */
            $recipe = $form->getData();
            $oldThumbnail = $recipe->getThumbnail();

            $file = $form->get('thumbnailfile')->getData();
            if ($file) $thumbnailPath = $this->fileUploader->uploadRecipeThumbnail($file);

            $fileDir = $this->getParameter('kernel.project_dir').'/public';
            $this->fileUploader->deleteThumbnail($fileDir,$oldThumbnail);

            /** @var Recipe $recipe */
            if($thumbnailPath) $recipe->setThumbnail($thumbnailPath);

            $entityManager->persist($recipe);
            $entityManager->flush();
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
    public function delete(Recipe $recipe, EntityManagerInterface $entityManager) {
        $currentThumbnail = $recipe->getThumbnail();
        $fileDir = $this->getParameter('kernel.project_dir').'/public';
        $this->fileUploader->deleteThumbnail($fileDir,$currentThumbnail);

        $entityManager->remove($recipe);
        $entityManager->flush();
        $this->addFlash('success', 'Recipe '.$recipe->getTitle().' deleted !');
        return $this->redirectToRoute('admin.recipe.index');
    }
}
