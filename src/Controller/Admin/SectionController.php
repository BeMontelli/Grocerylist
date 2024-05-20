<?php

namespace App\Controller\Admin;

use App\Entity\Section;
use App\Form\SectionType;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/sections", name: "admin.section.")]
#[IsGranted('ROLE_ADMIN')]
class SectionController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, SectionRepository $sectionRepository): Response
    {
        $currentPage = $request->query->getInt('page', 1);
        $sections = $sectionRepository->paginateSections($currentPage);

        return $this->render('admin/section/index.html.twig', [
            'sections' => $sections
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $section = new Section();
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($section);
            $entityManager->flush();
            $this->addFlash('success', 'Section saved !');

            return $this->redirectToRoute('admin.section.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/section/new.html.twig', [
            'section' => $section,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(Section $section): Response
    {
        return $this->render('admin/section/show.html.twig', [
            'section' => $section,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Section $section, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Section updated !');

            return $this->redirectToRoute('admin.section.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/section/edit.html.twig', [
            'section' => $section,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    public function delete(Request $request, Section $section, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$section->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($section);
            $entityManager->flush();
            $this->addFlash('success', 'Recipe '.$section->getTitle().' deleted !');
        }

        return $this->redirectToRoute('admin.section.index', [], Response::HTTP_SEE_OTHER);
    }
}