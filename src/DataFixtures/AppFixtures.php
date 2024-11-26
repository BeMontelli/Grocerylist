<?php

namespace App\DataFixtures;

/* Purge && reset db */
// php bin/console doctrine:database:drop --force
// php bin/console doctrine:database:create
// php bin/console doctrine:migrations:migrate

/* Fixtures commands */
// php bin/console doctrine:fixtures:load // purge && load
// php bin/console doctrine:fixtures:load --append
// php bin/console doctrine:fixtures:load --append --group=UserFixtures

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        //$manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,

            SectionFixtures::class,
            // Ingredients

            CategoryFixtures::class,
            // Recipes
        ];
    }
}
