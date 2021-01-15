<?php

namespace App\DataFixtures\Tests;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

abstract class BaseTestFixture extends Fixture  implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['test'];
    }
}
