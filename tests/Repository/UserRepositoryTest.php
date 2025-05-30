<?php

namespace App\Tests\Repository;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{

    // LiipTestFixturesBundle to load fixtures in tests

    public function testCount(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $users = $container->get(UserRepository::class)->count([]);
        $this->assertEquals(0, $users, 'There should be 0 users in the database before fixtures');
    }
}