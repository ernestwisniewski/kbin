<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\EntryCommentDto;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Entity\EntryComment;

class EntryCommentFixtures extends BaseFixture implements DependentFixtureInterface
{
    const COMMENTS_COUNT = 3000;

    private EntryCommentManager $commentManager;

    public function __construct(EntryCommentManager $commentManager)
    {
        $this->commentManager = $commentManager;
    }

    public function getDependencies()
    {
        return [
            EntryFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        foreach ($this->provideRandomComments(self::COMMENTS_COUNT) as $index => $comment) {
            $dto = (new EntryCommentDto())->create(
                $comment['body'],
                $comment['entry']
            );

            $entity = $this->commentManager->createComment($dto, $comment['user']);

            $manager->persist($entity);

            $this->addReference('comment'.'_'.$index, $entity);
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
