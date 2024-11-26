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

    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const NORMAL_USER_REFERENCE = 'normal-user';

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
                'reference' => self::ADMIN_USER_REFERENCE,
            ],
            [
                "username" => "benjmontellimard",
                "email" => "benjmontellimard@gmail.com",
                "password" => "gaD&c@J6nzMTsU",
                "roles" => ['ROLE_USER'],
                "verified" => true,
                'reference' => self::NORMAL_USER_REFERENCE,
            ],
        ];

        foreach ($users as $user) {
            $existing = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $user["email"]]);
    
            if (!$existing) $this->createUser($manager,$user,);
        }
    }

    public function createUser(ObjectManager $manager,array $user): void {
        $newUser = new User();
        $newUser->setUsername($user["username"]);
        $newUser->setEmail($user["email"]);
        $newUser->setRoles($user["roles"]);
        $newUser->setVerified($user["verified"]);

        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $newUser,
            $user['password']
        );
        $newUser->setPassword($hashedPassword);

        $manager->persist($newUser);
        $manager->flush();

        $this->addReference($user['reference'], $newUser);
    }

    public static function getGroups(): array
    {
        return ['all', 'users'];
    }
}
