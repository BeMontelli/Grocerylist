<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Section;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Service\DataImportService;

class SectionFixtures extends Fixture
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
            $this->dataImportService->createSections($user);
        }
    }

    public static function getGroups(): array
    {
        return ['all', 'sections','ingredients'];
    }
}
