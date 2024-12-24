<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Service\DataImportService;

class CategoryFixtures extends Fixture
{
    private EntityManagerInterface $entityManager;
    private DataImportService $dataImportService;

    public function __construct(EntityManagerInterface $entityManager, DataImportService $dataImportService)
    {
        $this->entityManager = $entityManager;
        $this->dataImportService = $dataImportService;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            $this->getReference(UserFixtures::ADMIN_USER_REFERENCE),
            $this->getReference(UserFixtures::NORMAL_USER_REFERENCE),
        ];

        foreach ($users as $user) {
            $this->dataImportService->createCategories($user);
        }
    }

    public static function getGroups(): array
    {
        return ['all', 'categories','recipes'];
    }
}
