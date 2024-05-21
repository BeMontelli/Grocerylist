<?php

namespace App\Controller\API;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/v1/categories", name: "api.category.")]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, CategoryRepository $categoryRepository, SerializerInterface $serializer): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->json($categories,Response::HTTP_OK, [], [
            'groups' => ['categories.*','categories.index']
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(int $id, CategoryRepository $categoryRepository): Response
    {
        return $this->json($categoryRepository->find($id),Response::HTTP_OK, [], [
            'groups' => ['categories.*','categories.show']
        ]);
    }

    #[Route('/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/category/edit.html.twig',[
            '' => ''
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Category $category, EntityManagerInterface $entityManager) {
        $entityManager->remove($category);
        $entityManager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
