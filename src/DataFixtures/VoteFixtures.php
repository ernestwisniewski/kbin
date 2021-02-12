<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Service\VoteManager;

class VoteFixtures extends BaseFixture implements DependentFixtureInterface
{
    private VoteManager $voteManager;

    public function __construct(VoteManager $voteManager)
    {
        $this->voteManager = $voteManager;
    }

    public function loadData(ObjectManager $manager)
    {
        for ($u = 0; $u <= UserFixtures::USERS_COUNT; $u++) {
            $randomNb = $this->getUniqueNb(
                EntryFixtures::ENTRIES_COUNT,
                intval(EntryFixtures::ENTRIES_COUNT / rand(2, 5))
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

            $randomNb = $this->getUniqueNb(
                EntryCommentFixtures::COMMENTS_COUNT,
                intval((EntryCommentFixtures::COMMENTS_COUNT / 5) / rand(2, 5))
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
    }

    public function getDependencies()
    {
        return [
            EntryFixtures::class,
            EntryCommentFixtures::class,
        ];
    }

    private function getUniqueNb(int $max, int $quantity): array
    {
        $numbers = range(1, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, $quantity);
    }
}
