<?php declare(strict_types = 1);

namespace App\DataFixtures;

use App\Service\VoteManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VoteFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function __construct(private VoteManager $voteManager)
    {
    }

    public function loadData(ObjectManager $manager): void
    {
        for ($u = 0; $u <= UserFixtures::USERS_COUNT; $u++) {
            $this->entries($u);
            $this->entryComments($u);
            $this->posts($u);
            $this->postComments($u);
        }
    }

    private function entries(int $u)
    {
        $randomNb = $this->getUniqueNb(
            EntryFixtures::ENTRIES_COUNT,
            intval(rand(0, 155))
        );

        foreach ($randomNb as $e) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $this->voteManager->vote(
                rand(0, 4) > 0 ? 1 : -1,
                $this->getReference('entry_'.$e),
                $this->getReference('user_'.$u)
            );
        }
    }

    private function getUniqueNb(int $max, int $quantity): array
    {
        $numbers = range(1, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, $quantity);
    }

    private function entryComments(int $u)
    {
        $randomNb = $this->getUniqueNb(
            EntryCommentFixtures::COMMENTS_COUNT,
            intval(rand(0, 155))
        );

        foreach ($randomNb as $c) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $this->voteManager->vote(
                rand(0, 4) > 0 ? 1 : -1,
                $this->getReference('entry_comment_'.$c),
                $this->getReference('user_'.$u)
            );
        }
    }

    private function posts(int $u)
    {
        $randomNb = $this->getUniqueNb(
            PostFixtures::ENTRIES_COUNT,
            intval(rand(0, 155))
        );

        foreach ($randomNb as $e) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $this->voteManager->vote(
                rand(0, 4) > 0 ? 1 : -1,
                $this->getReference('post_'.$e),
                $this->getReference('user_'.$u)
            );
        }
    }

    private function postComments(int $u)
    {
        $randomNb = $this->getUniqueNb(
            PostCommentFixtures::COMMENTS_COUNT,
            intval(rand(0, 155))
        );

        foreach ($randomNb as $c) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $this->voteManager->vote(
                rand(0, 4) > 0 ? 1 : -1,
                $this->getReference('post_comment_'.$c),
                $this->getReference('user_'.$u)
            );
        }
    }

    public function getDependencies(): array
    {
        return [
            EntryFixtures::class,
            EntryCommentFixtures::class,
            PostFixtures::class,
            PostCommentFixtures::class,
        ];
    }
}
