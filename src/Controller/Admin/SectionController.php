<?php

namespace App\Controller\Admin;

use App\Entity\Section;
use App\Entity\User;
use App\Form\SectionType;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/{_locale}/admin/sections", name: "admin.section.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class SectionController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, SectionRepository $sectionRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);
        $sections = $sectionRepository->paginateUserSections($currentPage,$user);

        // form new
        $section = new Section();
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $section->setUser($user);
                $entityManager->persist($section);
                $entityManager->flush();
                $this->addFlash('success', 'Section saved !');
                return $this->redirectToRoute('admin.section.index', [], Response::HTTP_SEE_OTHER);
            } else $this->addFlash('danger', 'Form validation error !');
        }

        return $this->render('admin/section/index.html.twig', [
            'sections' => $sections,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(Section $section): RedirectResponse
    {
        return $this->redirectToRoute('admin.section.edit', ["id" => $section->getId()]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Section $section, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'Section updated !');
                return $this->redirectToRoute('admin.section.index', [], Response::HTTP_SEE_OTHER);
            } else $this->addFlash('danger', 'Form validation error !');
        }

        return $this->render('admin/section/edit.html.twig', [
            'section' => $section,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Request $request, Section $section, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$section->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($section);
            $entityManager->flush();
            $this->addFlash('warning', 'Ingredient category '.$section->getTitle().' deleted !');
        } else {
            $this->addFlash('danger', 'Error occured !');
        }

        return $this->redirectToRoute('admin.section.index', [], Response::HTTP_SEE_OTHER);
    }
}
