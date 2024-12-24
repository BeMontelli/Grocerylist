<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Section;
use App\Entity\Ingredient;
use App\Repository\SectionRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Service\DataImportService;

class IngredientFixtures extends Fixture
{
    private EntityManagerInterface $entityManager;
    private SectionRepository $sectionRepository;
    private DataImportService $dataImportService;

    public function __construct(EntityManagerInterface $entityManager, SectionRepository $sectionRepository, DataImportService $dataImportService)
    {
        $this->entityManager = $entityManager;
        $this->sectionRepository = $sectionRepository;
        $this->dataImportService = $dataImportService;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            $this->getReference(UserFixtures::ADMIN_USER_REFERENCE),
            $this->getReference(UserFixtures::NORMAL_USER_REFERENCE),
        ];
        
        foreach ($users as $user) {
            $sections = $this->sectionRepository->findAllByUser($user,true);
            $this->dataImportService->createIngredients($user,$sections);
        }
    }

    public static function getGroups(): array
    {
        return ['all', 'ingredients', 'sections'];
    }
}
