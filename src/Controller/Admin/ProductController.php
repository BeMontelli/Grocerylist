<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/{_locale}/admin/products", name: "admin.product.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);
        $products = $productRepository->paginateUserProducts($currentPage,$user);

        return $this->render('admin/product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/create/', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $product = new Product();
        $form = $this->createForm(ProductType::class,$product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $product->setUser($user);
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Product saved !');
            return $this->redirectToRoute('admin.product.index');
        }

        return $this->render('admin/product/new.html.twig',[
            'form' => $form
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(string $slug, int $id, ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/show.html.twig', [
            'product' => $productRepository->find($id),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $entityManager->persist($formData);
            $entityManager->flush();
            $this->addFlash('success', 'Product updated !');
            return $this->redirectToRoute('admin.product.edit', ["id" => $product->getId()]);
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $entityManager) {
        $entityManager->remove($product);
        $entityManager->flush();
        $this->addFlash('success', 'Product '.$product->getTitle().' deleted !');
        return $this->redirectToRoute('admin.product.index');
    }
}
