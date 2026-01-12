<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Main fixture loader that orchestrates all other fixtures
     */
    public function load(ObjectManager $manager): void
    {
        // All fixtures are loaded through dependencies
    }

    /**
     * Define the order of fixture loading
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
            OrderFixtures::class,
        ];
    }
}
