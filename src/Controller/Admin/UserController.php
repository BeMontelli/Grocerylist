<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route("/{_locale}/admin/users", name: "admin.user.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                
                $emailConfirm = $form->get('emailconfirm')->getData();
                $passwordConfirm = $form->get('passwordconfirm')->getData();

                $newEmail = $form->get('email')->getData();
                if ($newEmail && $newEmail !== $user->getEmail()) {
                    if ($newEmail === $emailConfirm) {
                        $user->setEmail($newEmail);
                    } else {
                        $this->addFlash('danger', 'Email confirmation does not match!');
                        return $this->redirectToRoute('edit', ['id' => $user->getId()]);
                    }
                }

                $newPassword = $form->get('password')->getData();
                if ($newPassword && !$userPasswordHasher->isPasswordValid($user, $newPassword)) {
                    if ($newPassword === $passwordConfirm) {
                        $hashedPassword = $userPasswordHasher->hashPassword($user, $newPassword);
                        $user->setPassword($hashedPassword);
                    } else {
                        $this->addFlash('danger', 'Password confirmation does not match!');
                        return $this->redirectToRoute('edit', ['id' => $user->getId()]);
                    }
                }

                $em->flush();

                $this->addFlash('success', 'Changes saved !');
                return $this->redirectToRoute('admin.user.index', [], Response::HTTP_SEE_OTHER);
            } else $this->addFlash('danger', 'Form validation error !');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.user.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        return $this->render('admin/user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
