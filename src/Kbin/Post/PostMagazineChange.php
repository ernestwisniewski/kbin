<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Magazine;
use App\Entity\Post;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostMagazineChange
{
    public function __construct(
        private PostRepository $postRepository,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post, Magazine $magazine): void
    {
        $this->entityManager->beginTransaction();

        try {
            $oldMagazine = $post->magazine;
            $post->magazine = $magazine;

            foreach ($post->comments as $comment) {
                $comment->magazine = $magazine;
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return;
        }

        $oldMagazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($oldMagazine);
        $oldMagazine->postCount = $this->postRepository->countPostsByMagazine($oldMagazine);

        $magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($magazine);
        $magazine->postCount = $this->postRepository->countPostsByMagazine($magazine);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));
    }
}
