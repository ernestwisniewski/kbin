<?php declare(strict_types = 1);

namespace App\DataFixtures;

use App\Utils\Slugger;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class BaseFixture extends Fixture implements FixtureGroupInterface
{
    protected Generator $faker;
    protected ObjectManager $manager;
    protected Slugger $slugger;

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker   = Factory::create();

        $this->loadData($manager);
    }

    abstract protected function loadData(ObjectManager $manager);

    protected function camelCase($value): string
    {
        return Slugger::camelCase($value);
    }

    protected function getRandomTime(?DateTimeImmutable $from = null): DateTimeImmutable
    {
        return new DateTimeImmutable(
            $this->faker->dateTimeBetween
            (
                $from ? $from->format('Y-m-d H:i:s') : '-1 month',
                'now'
            )
                ->format('Y-m-d H:i:s')
        );
    }
}
