<?php declare(strict_types = 1);

namespace App\DataFixtures;

use App\DTO\PostCommentDto;
use App\Entity\PostComment;
use App\Service\PostCommentManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostCommentFixtures extends BaseFixture implements DependentFixtureInterface
{
    const COMMENTS_COUNT = EntryFixtures::ENTRIES_COUNT * 3;

    private PostCommentManager $postCommentManager;

    public function __construct(PostCommentManager $postCommentManager)
    {
        $this->postCommentManager = $postCommentManager;
    }

    public function getDependencies(): array
    {
        return [
            PostFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager): void
    {
        foreach ($this->provideRandomComments(self::COMMENTS_COUNT) as $index => $comment) {
            $dto       = new PostCommentDto();
            $dto->post = $comment['post'];
            $dto->body = $comment['body'];

            $entity = $this->postCommentManager->create($dto, $comment['user']);

            $manager->persist($entity);

            $this->addReference('post_comment_'.$index, $entity);
            $manager->flush();

            $roll     = rand(0, 4);
            $children = [$entity];
            if ($roll) {
                for ($i = 1; $i <= rand(0, 20); $i++) {
                    $children[] = $this->createChildren($children[array_rand($children, 1)], $manager);
                }
            }

            $entity->createdAt = $this->getRandomTime($entity->post->createdAt);
            $entity->updateLastActive();
        }

        $manager->flush();
    }

    private function provideRandomComments($count = 1): iterable
    {
        for ($i = 0; $i <= $count; $i++) {
            yield [
                'body' => $this->faker->realText($this->faker->numberBetween(10, 1024)),
                'post' => $this->getReference('post_'.rand(1, EntryFixtures::ENTRIES_COUNT)),
                'user' => $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)),
            ];
        }
    }

    private function createChildren(PostComment $parent, ObjectManager $manager): PostComment
    {
        $dto = (new PostCommentDto())->createWithParent(
            $parent->post,
            $parent,
            null,
            $this->faker->realText($this->faker->numberBetween(10, 1024))
        );

        $entity = $this->postCommentManager->create($dto, $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT)));


        $entity->createdAt = $this->getRandomTime($parent->createdAt);
        $entity->updateLastActive();

        $manager->flush();

        return $entity;
    }
}
