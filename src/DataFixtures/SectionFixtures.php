<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Section;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SectionFixtures extends Fixture
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

        $sections = [
            "Conserves",
            "Hygiene",
            "Ménage",
            "Vaisselle",
            "Cuisine",
            "Matin/Biscuits",
            "Boissons",
            "Desserts",
            "Apéritifs",
            "Surgelés",
            "Condiments/Sauces",
            "Épicerie",
            "Fruits",
            "Légumes",
            "Laitages",
            "Fromages",
            "Viandes",
            "Poissons",
            "Autres"
        ];

        foreach ($users as $user) {
            foreach ($sections as $section) {
                $existing = $this->entityManager->getRepository(Section::class)
                    ->findOneBy(['title' => $section,'user' => $user]);
        
                if (!$existing) $this->createSection($manager,$section,$user);
            }
        }
    }

    public function createSection(ObjectManager $manager,string $section,$user): void {
        $slugger = new AsciiSlugger();

        $newSection = new Section();
        $newSection->setTitle($section);

        $slug = strtolower($slugger->slug($section));
        $newSection->setSlug($slug);

        $newSection->setUser($user);

        $newSection->setCreatedAt(new \DateTimeImmutable());
        $newSection->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($newSection);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'sections','ingredients'];
    }
}
