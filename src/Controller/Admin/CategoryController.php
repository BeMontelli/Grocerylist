<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route("/{_locale}/admin/categories", name: "admin.category.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var $user User */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);
        $categories = $categoryRepository->paginateCategories($currentPage);

        // form new
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $category->setUser($user);
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Category saved !');
            return $this->redirectToRoute('admin.category.index');
        }

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'form' => $form
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(string $slug, int $id, CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $categoryRepository->find($id),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $entityManager->persist($formData);
            $entityManager->flush();
            $this->addFlash('success', 'Category updated !');
            return $this->redirectToRoute('admin.category.edit', ["id" => $category->getId()]);
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Category $category, EntityManagerInterface $entityManager) {
        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash('success', 'Category '.$category->getTitle().' deleted !');
        return $this->redirectToRoute('admin.category.index');
    }
}
