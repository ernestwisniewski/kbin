<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Utils\Slugger;
use Faker\Generator;
use Faker\Factory;

abstract class BaseFixture extends Fixture implements FixtureGroupInterface
{
    protected Generator $faker;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->faker   = Factory::create('pl_PL');

        $this->loadData($manager);
    }

    abstract protected function loadData(ObjectManager $manager);

    public static function getGroups(): array
    {
        return ['dev'];
    }

    protected function camelCase($value): string
    {
        $slugger = new Slugger();

        return $slugger->camelCase($value);
    }
}
