<?php

namespace App\Controller\Admin;

use App\Entity\Group;
use App\Entity\User;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/{_locale}/admin/groups", name: "admin.group.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class GroupController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, GroupRepository $groupRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var $user User */
        $user = $this->security->getUser();

        $currentPage = $request->query->getInt('page', 1);
        $groups = $groupRepository->paginateUserGroups($currentPage,$user);

        // form new
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group->setUser($user);
            $entityManager->persist($group);
            $entityManager->flush();
            $this->addFlash('success', 'Group saved !');

            return $this->redirectToRoute('admin.group.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/group/index.html.twig', [
            'groups' => $groups,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(Group $group): RedirectResponse
    {
        return $this->redirectToRoute('admin.group.edit', ["id" => $group->getId()]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Group updated !');

            return $this->redirectToRoute('admin.group.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/group/edit.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    public function delete(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($group);
            $entityManager->flush();
            $this->addFlash('success', 'Ingredient category '.$group->getTitle().' deleted !');
        }

        return $this->redirectToRoute('admin.group.index', [], Response::HTTP_SEE_OTHER);
    }
}
