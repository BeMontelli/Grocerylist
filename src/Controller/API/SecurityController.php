<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Entity\User;

#[Route("/api/v1/security", name: "api.security.")]
class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TokenGeneratorInterface $tokenGenerator): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if (!isset($username, $password)) {
            return $this->json(['error' => 'Username and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $tokenGenerator->generateToken();

        $user->setApiToken($token);
        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getEmail(),
            'token' => $token,
            'message' => 'Login successful',
        ], Response::HTTP_OK);
    }

    /*#[Route(path: '/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TokenGeneratorInterface $tokenGenerator): Response
    {
        $token = $request->request->get('token');
        
        if (!isset($token)) {
            return $this->json(['error' => 'Token is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['apitoken' => $token]);

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $user->setApiToken(null);
        $entityManager->flush();

        return $this->json([
            'email' => $user->getEmail(),
            'message' => 'Logout successful',
        ], Response::HTTP_OK);
    }*/
}