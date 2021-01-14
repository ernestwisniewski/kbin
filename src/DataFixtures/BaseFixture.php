<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;

abstract class BaseFixture extends Fixture
{
    /**
     * @var Generator $faker
     */
    protected $faker;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->faker   = Factory::create();

        $this->loadData($manager);
    }

    abstract protected function loadData(ObjectManager $manager);
}
