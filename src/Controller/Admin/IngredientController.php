<?php

namespace App\Controller\Admin;

use App\DTO\SearchIngredientsDTO;
use App\Entity\Ingredient;
use App\Entity\GroceryList;
use App\Entity\GroceryListIngredient;
use App\Entity\User;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\GroceryListIngredientService;
use App\Form\SearchIngredientsType;

#[Route("/{_locale}/admin/products-ingredients", name: "admin.ingredient.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class IngredientController extends AbstractController
{
    private $security;
    private $translator;

    public function __construct(Security $security,TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request, IngredientRepository $ingredientRepository, EntityManagerInterface $entityManager, GroceryListIngredientService $groceryListIngredientService): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);

        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class,$ingredient,[
            'user'=> $user,
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if($form->isValid()) {
                $ingredient->setUser($user);
    
                $entityManager->persist($ingredient);
                $entityManager->flush();
    
                $groceryListIngredientService->linkIngredientToGroceryLists(
                    $ingredient,
                    null,
                    $ingredient->getTemporaryGroceryLists()
                );
    
                $this->addFlash('success', $this->translator->trans('app.notif.saved', [
                    '%entity%' => $this->translator->trans('app.admin.ingredients.entity',['%entity%' => '1']),
                    '%gender%' => 'male'
                ]));
                return $this->redirectToRoute('admin.ingredient.index');
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }

        $search = new SearchIngredientsDTO();
        $formSearch = $this->createForm(SearchIngredientsType::class,$search, [
            'attr' => ['data-turbo' => 'false'],
            'method' => 'GET',
        ]);
        $formSearch->handleRequest($request);
        if($formSearch->isSubmitted() && $formSearch->isValid()) {
            $title = $formSearch->get('title')->getData();
            $sections = $formSearch->get('sections')->getData();
            $ingredients = $ingredientRepository->paginateUserSearchedIngredients(1,$user,$title,$sections);
        } else {
            $ingredients = $ingredientRepository->paginateUserIngredients($currentPage,$user);
        }

        return $this->render('admin/ingredient/index.html.twig', [
            'ingredients' => $ingredients,
            'form' => $form,
            'formSearch' => $formSearch
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(string $slug, int $id, IngredientRepository $ingredientRepository): Response
    {
        $ingredient = $ingredientRepository->find($id);
        return $this->redirectToRoute('admin.ingredient.edit', ["id" => $ingredient->getId()]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $form = $this->createForm(IngredientType::class,$ingredient,[
            'user'=> $user,
        ]);
        
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if($form->isValid()) {
                $formData = $form->getData();
                $entityManager->persist($formData);
                $entityManager->flush();

                $groceryListsAssociated = [];
                if($ingredient->getId()) {
                    $groceryListsAssociated = $entityManager->getRepository(GroceryList::class)->getIngredientGroceryLists($ingredient);
                }
                foreach($groceryListsAssociated as $groceryList) {
                    $groceryList->setUpdatedAt(new \DateTimeImmutable());
                    $entityManager->persist($groceryList);
                }
                $entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.notif.edited',[
                    '%gender%' => 'male',
                    '%entity%' => $this->translator->trans('app.admin.ingredients.entity',['%entity%' => '1'])
                ]));
                return $this->redirectToRoute('admin.ingredient.edit', ["id" => $ingredient->getId()]);
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }

        return $this->render('admin/ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager) {
        
        if ($this->isCsrfTokenValid('delete'.$ingredient->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($ingredient);
            $entityManager->flush();
            $this->addFlash('warning', $ingredient->getTitle().': '.$this->translator->trans('app.notif.deleted', [
                '%entity%' => $this->translator->trans('app.admin.ingredients.entity',['%entity%' => '1']),
                '%gender%' => 'male'
            ]));
        } else {
            $this->addFlash('danger', $this->translator->trans('app.notif.erroccur'));
        }

        return $this->redirectToRoute('admin.ingredient.index');
    }
}
