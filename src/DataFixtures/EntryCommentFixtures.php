<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\EntryComment;

class EntryCommentFixtures extends BaseFixture implements DependentFixtureInterface
{
    const COMMENTS_COUNT = 90000;

    public function getDependencies()
    {
        return [
            EntryFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        foreach ($this->provideRandomComments(self::COMMENTS_COUNT) as $index => $comment) {
            $newEntry = new EntryComment(
                $comment['body'],
                $comment['entry'],
                $comment['user']
            );

            $manager->persist($newEntry);

            $this->addReference('comment'.'_'.$index, $newEntry);
        }

        $manager->flush();
    }

    private function provideRandomComments($count = 1): iterable
    {
        for ($i = 0; $i <= $count; $i++) {
            yield [
                'body'  => $this->faker->paragraphs($this->faker->numberBetween(1, 3), true),
                'entry' => $this->getReference('entry_'.rand(1, EntryFixtures::ENTRIES_COUNT)),
                'user'  => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }
}
