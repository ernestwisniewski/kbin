<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Kbin\Magazine\MagazineBlock;
use App\Kbin\Magazine\MagazineSubscribe;
use App\Kbin\User\UserBlock;
use App\Kbin\User\UserFollow;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SubFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly MagazineSubscribe $magazineSubscribe,
        private readonly MagazineBlock $magazineBlock,
        private readonly UserFollow $userFollow,
        private readonly UserBlock $userBlock
    ) {
    }

    public function loadData(ObjectManager $manager): void
    {
        for ($u = 1; $u <= UserFixtures::USERS_COUNT; ++$u) {
            $this->magazines($u);
            $this->users($u);
        }
    }

    private function magazines(int $u)
    {
        $randomNb = $this->getUniqueNb(
            MagazineFixtures::MAGAZINES_COUNT,
            \intval(MagazineFixtures::MAGAZINES_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $m) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                ($this->magazineBlock)(
                    $this->getReference('magazine_'.$m),
                    $this->getReference('user_'.$u)
                );
                continue;
            }

            ($this->magazineSubscribe)(
                $this->getReference('magazine_'.$m),
                $this->getReference('user_'.$u)
            );
        }
    }

    private function getUniqueNb(int $max, int $quantity): array
    {
        $numbers = range(1, $max);
        shuffle($numbers);

        return \array_slice($numbers, 0, $quantity);
    }

    private function users(int $u)
    {
        $randomNb = $this->getUniqueNb(
            UserFixtures::USERS_COUNT,
            \intval(UserFixtures::USERS_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $f) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                ($this->userBlock)(
                    $this->getReference('user_'.$f),
                    $this->getReference('user_'.$u)
                );
                continue;
            }

            ($this->userFollow)(
                $this->getReference('user_'.$f),
                $this->getReference('user_'.$u)
            );
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            MagazineFixtures::class,
        ];
    }
}
