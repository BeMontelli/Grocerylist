<?php

namespace App\Controller\Admin;

use App\DTO\SearchRecipesDTO;
use App\Entity\File;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\RecipeType;
use App\Entity\GroceryList;
use App\Entity\GroceryListIngredient;
use App\Repository\RecipeRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
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
use Doctrine\Persistence\Proxy;

#[Route("/{_locale}/admin/recipes", name: "admin.recipe.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class RecipeController extends AbstractController
{
    private $security;
    private $translator;
    private $fileUploader;
    private $groceryListIngredientService;

    public function __construct(Security $security, TranslatorInterface $translator, FileUploader $fileUploader,GroceryListIngredientService $groceryListIngredientService)
    {
        $this->security = $security;
        $this->translator = $translator;
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
        
        if($form->isSubmitted()) {
            if($form->isValid()) {
                $uploadfile = $form->get('uploadfile')->getData();
                $selectfile = $form->get('selectfile')->getData();

                /** @var Recipe $recipe */
                $recipe = $form->getData();

                if ($uploadfile) {
                    // if file uploaded, priority to this file
                    $newFile = $this->fileUploader->uploadFile($uploadfile, $user);
                    $em->persist($newFile);
                    $em->flush();

                    $recipe->setThumbnail($newFile);
                } else {
                    if (!empty($selectfile) && $selectfile instanceof File) {
                        // if no file uploaded but file selected => link file to recipe
                        $recipe->setThumbnail($selectfile);
                    }
                }
    
                $recipe = $form->getData();
                $recipe->setUser($user);
                $em->persist($recipe);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('app.notif.saved', [
                    '%entity%' => $this->translator->trans('app.admin.recipes.entity',['%entity%' => '1']),
                    '%gender%' => 'female'
                ]));
                return $this->redirectToRoute('admin.recipe.index');
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }

        $search = new SearchRecipesDTO();
        $formSearch = $this->createForm(SearchRecipesType::class,$search, [
            'attr' => ['data-turbo' => 'false'],
            'method' => 'GET',
        ]);
        $formSearch->handleRequest($request);
        if($formSearch->isSubmitted() && $formSearch->isValid()) {
            $title = $formSearch->get('title')->getData();
            $categories = $formSearch->get('categories')->getData();
            $recipes = $recipeRepository->paginateUserSearchedRecipes(1,$user,$title,$categories);
        } else {
            $order = ($request->query->get('order')) ? $request->query->get('order') : 'id';
            $recipes = $recipeRepository->paginateUserRecipes($currentPage,$user,$order);
        }
        
        // PROXY collection objects fully initialize if not
        foreach ($recipes as $recipe) {
            $thumbnail = $recipe->getThumbnail();
            if ($thumbnail instanceof Proxy) {
                $em->initializeObject($thumbnail);
            }
        }

        return $this->render('admin/recipe/index.html.twig', [
            'recipes' => $recipes,
            'form' => $form,
            'formSearch' => $formSearch,
            'isRandom' => (!empty($request->query->get('order')) && $request->query->get('order') === 'random'),
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
        ],['attr' => ['class' => 'narrow__form']]);
        
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
                $this->addFlash('danger', $this->translator->trans('app.notif.list.none'));
                return $this->redirectToRoute('admin.recipe.show', ['id' => $recipe->getId(),'slug' => $recipe->getSlug()]);
            }

            $groceryList->addRecipe($recipe);
            $groceryList->setUpdatedAt(updatedAt: new \DateTimeImmutable());
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
            $this->addFlash('success', $this->translator->trans('app.notif.list.addsuccess'));

            return $this->redirectToRoute('admin.recipe.show', ['id' => $recipe->getId(),'slug' => $recipe->getSlug()]);
        }

        // FORM edit recipe
        $form = $this->createForm(RecipeType::class, $recipe
        ,['attr' => ['class' => 'narrow__form']]);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if($form->isValid()) {
                /** @var Recipe $recipe */
                $recipe = $form->getData();

                $uploadfile = $form->get('uploadfile')->getData();
                $selectfile = $form->get('selectfile')->getData();

                /** @var Recipe $recipe */
                $recipe = $form->getData();

                if ($uploadfile) {
                    // if file uploaded, priority to this file
                    $newFile = $this->fileUploader->uploadFile($uploadfile, $user);
                    $em->persist($newFile);
                    $em->flush();
                    
                    $recipe->setThumbnail($newFile);
                } else {
                    if (!empty($selectfile) && $selectfile instanceof File) {
                        // if no file uploaded but file selected => link file to recipe
                        $recipe->setThumbnail($selectfile);
                    }
                }

                $em->persist($recipe);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('app.notif.edited',[
                    '%entity%' => $this->translator->trans('app.admin.recipes.entity',['%entity%' => '1']),
                    '%gender%' => 'female'
                ]));
                return $this->redirectToRoute('admin.recipe.show', ['id' => $recipe->getId(),'slug' => $recipe->getSlug()]);
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }
        
        // PROXY object fully initialize if not
        $thumbnail = $recipe->getThumbnail();
        if ($thumbnail instanceof Proxy) {
            $em->initializeObject($thumbnail);
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

            /** @var GroceryListIngredient $groceryListIngredient */
            foreach ($recipe->getGroceryListIngredients() as $groceryListIngredient) {
                $em->remove($groceryListIngredient);
                $em->flush();
            }
            
            $em->remove($recipe);
            $em->flush();
            $this->addFlash('warning', $recipe->getTitle().': '.$this->translator->trans('app.notif.deleted', [
                '%entity%' => $this->translator->trans('app.admin.recipes.entity',['%entity%' => '1']),
                '%gender%' => 'female'
            ]));
        } else {
            $this->addFlash('danger', $this->translator->trans('app.notif.erroccur'));
        }

        return $this->redirectToRoute('admin.recipe.index');
    }
}
