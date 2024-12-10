<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Section;
use App\Entity\File;
use App\Entity\GroceryList;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\User;
use App\Form\UserCreateType;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use App\Service\FileUploader;

#[Route("/{_locale}/admin/users", name: "admin.user.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, EntityManagerInterface $em,UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, FileUploader $fileUploader): Response
    {
        $user = new User();
        $form = $this->createForm(UserCreateType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                
                $emailConfirm = $form->get('emailconfirm')->getData();
                $passwordConfirm = $form->get('passwordconfirm')->getData();

                $email = $form->get('email')->getData();
                $password = $form->get(name: 'password')->getData();

                $uploadfile = $form->get('uploadfile')->getData();
                $selectfile = $form->get('selectfile')->getData();

                if ($email === $emailConfirm && $password === $passwordConfirm) {
                    $user->setEmail($email);
                    $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                    $user->setRoles(['ROLE_USER']);
                    $user->setVerified(true);
    
                    /** @var Recipe $recipe */
                    $recipe = $form->getData();
    
                    if ($uploadfile) {
                        // if file uploaded, priority to this file
                        $newFile = $fileUploader->uploadFile($uploadfile, $user);
                        $em->persist($newFile);
                        $em->flush();
    
                        $recipe->setThumbnail($newFile);
                    } else {
                        if (!empty($selectfile) && $selectfile instanceof File) {
                            // if no file uploaded but file selected => link file to recipe
                            $recipe->setThumbnail($selectfile);
                        }
                    }

                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('success', 'Changes saved !');
                } else {
                    $this->addFlash('danger', 'Email or Password confirmation does not match!');
                }

                return $this->redirectToRoute('admin.user.index', [], Response::HTTP_SEE_OTHER);
            } else $this->addFlash('danger', 'Form validation error !');
        }

        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, FileUploader $fileUploader, Security $security): Response
    {
        /** @var User $userLogged */
        $userLogged = $security->getUser();

        $redirectRoute = 'admin.user.index';
        if(
            $userLogged->getId() !== $user->getId()
            && !in_array('ROLE_ADMIN', $userLogged->getRoles(), true)
        ) {
            $redirectRoute = 'admin.dashboard';
            return $this->redirectToRoute($redirectRoute, [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserEditType::class, $user);
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

                $uploadfile = $form->get('uploadfile')->getData();
                $selectfile = $form->get('selectfile')->getData();

                /** @var User $user */
                $user = $form->getData();

                if ($uploadfile) {
                    // if file uploaded, priority to this file
                    $newFile = $fileUploader->uploadFile($uploadfile, $user);
                    $em->persist($newFile);
                    $em->flush();
                    
                    $user->setPicture($newFile);
                } else {
                    if (!empty($selectfile) && $selectfile instanceof File) {
                        // if no file uploaded but file selected => link file to recipe
                        $user->setPicture($selectfile);
                    }
                }

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Changes saved !');
                return $this->redirectToRoute($redirectRoute, [], Response::HTTP_SEE_OTHER);
            } else $this->addFlash('danger', 'Form validation error !');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            
            /** @var GroceryList $grocerylist */
            foreach ($user->getGroceryLists() as $grocerylist) {
                foreach ($grocerylist->getGroceryListIngredients() as $groceryListIngredient) {
                    $em->remove($groceryListIngredient);
                }
                $em->remove($grocerylist);
                $em->flush();
            }

            /** @var Ingredient $ingredient */
            foreach ($user->getIngredients() as $ingredient) {
                $em->remove($ingredient);
                $em->flush();
            }
            
            /** @var Recipe $recipe */
            foreach ($user->getRecipes() as $recipe) {
                $em->remove($recipe);
                $em->flush();
            }

            /** @var Section $section */
            foreach ($user->getSections() as $section) {
                $em->remove($section);
                $em->flush();
            }

            /** @var Category $category */
            foreach ($user->getCategories() as $category) {
                $em->remove($category);
                $em->flush();
            }

            // WIP remove user thumbnail if exist
            /** @var File $file */
            foreach ($user->getFiles() as $file) {
                $currentThumbnail = $file->getUrl();
                if(!empty($currentThumbnail)) {
                    $fileDir = $this->getParameter('kernel.project_dir').'/public';
                    $fileUploader->deleteThumbnail($fileDir,$currentThumbnail);
                }
                $em->remove($file);
                $em->flush();
            }

            $em->remove($user);
            $em->flush();
            $this->addFlash('warning', 'User '.$user->getUsername().' deleted !');
        } else {
            $this->addFlash('danger', 'Error occured !');
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
