<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PostComment;
use App\Kbin\PostComment\DTO\PostCommentDto;
use App\Kbin\PostComment\PostCommentCreate;
use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class PostCommentFixtures extends BaseFixture implements DependentFixtureInterface
{
    public const COMMENTS_COUNT = EntryFixtures::ENTRIES_COUNT * 3;

    public function __construct(
        private readonly PostCommentCreate $postCommentCreate,
        private readonly ImageManager $imageManager,
        private readonly ImageRepository $imageRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
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
            $dto = new PostCommentDto();
            $dto->post = $comment['post'];
            $dto->body = $comment['body'];
            $dto->lang = 'en';

            $entity = ($this->postCommentCreate)($dto, $comment['user']);

            $manager->persist($entity);

            $this->addReference('post_comment_'.$index, $entity);
            $manager->flush();

            $roll = rand(0, 4);
            $children = [$entity];
            if ($roll) {
                for ($i = 1; $i <= rand(0, 20); ++$i) {
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
        for ($i = 0; $i <= $count; ++$i) {
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
        $dto->lang = 'en';

        $entity = ($this->postCommentCreate)(
            $dto,
            $this->getReference('user_'.rand(1, UserFixtures::USERS_COUNT))
        );

        $roll = rand(1, 400);
        if ($roll % 10) {
            try {
                $tempFile = $this->imageManager->download("https://picsum.photos/300/$roll?hash=$roll");
            } catch (\Exception $e) {
                $tempFile = null;
            }

            if ($tempFile) {
                $image = $this->imageRepository->findOrCreateFromPath($tempFile);

                $entity->image = $image;
                $this->entityManager->flush();
            }
        }

        $entity->createdAt = $this->getRandomTime($parent->createdAt);
        $entity->updateLastActive();

        $manager->flush();

        return $entity;
    }
}
