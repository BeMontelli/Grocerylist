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

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        //$manager->flush();
    }
}
