<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use Symfony\Component\String\Slugger\AsciiSlugger;

class CategoryFixtures extends Fixture
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

        $categories = [
            "Entrée",
            "Plat principal",
            "Dessert",
            "Apéritif",
            "Boisson",
            "Autre"
        ];

        foreach ($users as $user) {
            foreach ($categories as $category) {
                $existing = $this->entityManager->getRepository(Category::class)
                    ->findOneBy(['title' => $category,'user' => $user]);
        
                if (!$existing) $this->createCategory($manager,$category,$user);
            }
        }
    }

    public function createCategory(ObjectManager $manager,string $category,$user): void {
        $slugger = new AsciiSlugger();

        $newCategory = new Category();
        $newCategory->setTitle($category);

        $slug = strtolower($slugger->slug($category));
        $newCategory->setSlug($slug);

        $newCategory->setUser($user);

        $newCategory->setCreatedAt(new \DateTimeImmutable());
        $newCategory->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($newCategory);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'categories','recipes'];
    }
}
