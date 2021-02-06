<?php declare(strict_types = 1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Service\EntryCommentManager;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;

class EntryCommentFixtures extends BaseFixture implements DependentFixtureInterface
{
    const COMMENTS_COUNT = EntryFixtures::ENTRIES_COUNT * 5;

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
                $comment['entry'],
                $comment['body']
            );

            $entity = $this->commentManager->create($dto, $comment['user']);

            $manager->persist($entity);

            $this->addReference('entry_comment_'.$index, $entity);
            $manager->flush();

            $roll = rand(0,4);
            $children = [$entity];
            if($roll) {
                for ($i = 1; $i <= rand(0,20); $i++) {
                    $children[] = $this->createChildren($children[array_rand($children, 1)]);
                }
            }
        }

        $manager->flush();
    }

    private function createChildren(EntryComment $parent): EntryComment {

        $dto = (new EntryCommentDto())->createWithParent(
            $parent->getEntry(),
            $parent,
            $this->faker->paragraphs($this->faker->numberBetween(1, 3), true)
        );

        return $this->commentManager->create($dto, $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)));
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
