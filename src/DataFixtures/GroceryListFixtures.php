<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\GroceryList;
use App\Entity\User;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\DataFixtures\UserFixtures;

class GroceryListFixtures extends Fixture
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            $this->getReference(UserFixtures::ADMIN_USER_REFERENCE),
            $this->getReference(UserFixtures::NORMAL_USER_REFERENCE),
        ];

        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) {
                $this->createGroceryList($manager, $i, $user);
            }
        }
    }

    private function createGroceryList(ObjectManager $manager, int $index, User $user): void
    {
        $groceryList = new GroceryList();
        $groceryList->setTitle('List ' . $user->getId() . '_' . $index);
        $groceryList->setSlug((new AsciiSlugger())->slug($groceryList->getTitle()));
        $groceryList->setUser($user);
        $groceryList->setCreatedAt(new \DateTimeImmutable());
        $groceryList->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($groceryList);
        $manager->flush();
        
        $this->addReference('grocery_list_' . $user->getId() . '_' . $index, $groceryList);
    }

    public static function getGroups(): array
    {
        return ['all', 'grocerylist'];
    }
}
