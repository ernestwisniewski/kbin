<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\EntryCommentReport;
use App\Entity\EntryReport;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReportFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager): void
    {
        for ($u = 0; $u <= UserFixtures::USERS_COUNT; $u++) {
            $this->entries($u);
            $this->entryComments($u);
            $this->posts($u);
            $this->postComments($u);
        }

        $this->manager->flush();
    }

    private function getUniqueNb(int $max, int $quantity): array
    {
        $numbers = range(1, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, $quantity);
    }

    public function getDependencies(): array
    {
        return [
            EntryCommentFixtures::class,
            PostCommentFixtures::class,
        ];
    }

    private function entries(int $u)
    {
        $randomNb = $this->getUniqueNb(
            EntryFixtures::ENTRIES_COUNT,
            intval(EntryFixtures::ENTRIES_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $e) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new EntryReport(
                $this->getReference('user_'.$u),
                $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                $this->getReference('entry_'.$e)
            );

            $this->manager->persist($r);
        }
    }

    private function entryComments(int $u)
    {
        $randomNb = $this->getUniqueNb(
            EntryCommentFixtures::COMMENTS_COUNT,
            intval(EntryCommentFixtures::COMMENTS_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $c) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new EntryCommentReport(
                $this->getReference('user_'.$u),
                $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                $this->getReference('entry_comment_'.$c)
            );

            $this->manager->persist($r);
        }
    }

    private function posts(int $u)
    {
        $randomNb = $this->getUniqueNb(
            PostFixtures::ENTRIES_COUNT,
            intval(PostFixtures::ENTRIES_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $e) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new EntryCommentReport(
                $this->getReference('user_'.$u),
                $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                $this->getReference('post_'.$e)
            );

            $this->manager->persist($r);
        }
    }

    private function postComments(int $u)
    {
        $randomNb = $this->getUniqueNb(
            PostCommentFixtures::COMMENTS_COUNT,
            intval(PostCommentFixtures::COMMENTS_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $c) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new EntryCommentReport(
                $this->getReference('user_'.$u),
                $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
                $this->getReference('post_comment_'.$c)
            );

            $this->manager->persist($r);
        }
    }
}
