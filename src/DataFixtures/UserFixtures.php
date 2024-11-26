<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                "username" => "admin",
                "email" => "adminlists@montellimard.fr",
                "password" => "CU4JpB&o8jQiMq",
                "roles" => ['ROLE_USER', 'ROLE_ADMIN'],
                "verified" => true,
            ],
            [
                "username" => "benjmontellimard",
                "email" => "benjmontellimard@gmail.com",
                "password" => "gaD&c@J6nzMTsU",
                "roles" => ['ROLE_USER'],
                "verified" => true,
            ],
        ];

        foreach ($users as $user) {
            $existing = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $user["email"]]);
    
            if (!$existing) $this->createUser($manager,$user,);
        }
    }

    public function createUser(ObjectManager $manager,array $user): void {
        $userAdmin = new User();
        $userAdmin->setUsername($user["username"]);
        $userAdmin->setEmail($user["email"]);
        $userAdmin->setRoles($user["roles"]);
        $userAdmin->setVerified($user["verified"]);

        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $userAdmin,
            $user['password']
        );
        $userAdmin->setPassword($hashedPassword);

        $manager->persist($userAdmin);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'users'];
    }
}
