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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/{_locale}/admin/lists", name: "admin.list.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class GroceryListController extends AbstractController
{
    private $security;
    private $fileUploader;

    public function __construct(Security $security, FileUploader $fileUploader)
    {
        $this->security = $security;
        $this->fileUploader = $fileUploader;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, GroceryListRepository $groceryListRepository): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);
        $lists = $groceryListRepository->paginateUserLists($currentPage,$user);
        return $this->render('admin/grocery_list/index.html.twig', [
            'grocery_lists' => $lists,
        ]);
    }

    #[Route('/new/', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $groceryList = new GroceryList();
        $form = $this->createForm(GroceryListType::class, $groceryList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groceryList->setUser($user);
            $entityManager->persist($groceryList);
            $entityManager->flush();
            $this->addFlash('success', 'List saved !');

            return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/grocery_list/new.html.twig', [
            'grocery_list' => $groceryList,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(string $slug, int $id,GroceryListRepository $groceryListRepository, EntityManagerInterface $entityManager): Response
    {
        $groceryList = $groceryListRepository->findWithIngredients($id);
        
        /** @var User $user  */
        $user = $this->security->getUser();
        $user->setCurrentGroceryList($groceryList);
        $entityManager->flush();

        return $this->render('admin/grocery_list/show.html.twig', [
            'grocery_list' => $groceryList,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, GroceryList $groceryList, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $form = $this->createForm(GroceryListType::class, $groceryList);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'List updated !');

            return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/grocery_list/edit.html.twig', [
            'grocery_list' => $groceryList,
            'form' => $form
        ]);
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
            $this->addFlash('success', 'List '.$groceryList->getTitle().' deleted !');
        }

        return $this->redirectToRoute('admin.list.index', [], Response::HTTP_SEE_OTHER);
    }
}
