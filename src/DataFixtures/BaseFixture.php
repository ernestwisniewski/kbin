<?php declare(strict_types=1);

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
    protected ObjectManager $manager;

    public function load(ObjectManager $manager): void
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
        return (new Slugger())->camelCase($value);
    }

    protected function getRandomTime(?\DateTimeImmutable $from = null): \DateTimeImmutable
    {
        return new \DateTimeImmutable(
            $this->faker->dateTimeBetween
            (
                $from ? $from->format('Y-m-d H:i:s') : '-1 month',
                'now'
            )
                ->format('Y-m-d H:i:s')
        );
    }
}
