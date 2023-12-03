<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Post\EventSubscriber;

use App\Entity\Post;
use App\Kbin\Post\EventSubscriber\Event\PostBeforeDeletedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostBeforePurgeEvent;
use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use App\Kbin\Post\PostMagazineChange;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Repository\MagazineRepository;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class PostActivityPubSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private DeleteWrapper $deleteWrapper,
        private PostMagazineChange $postMagazineChange,
        private MagazineRepository $magazineRepository,
    ) {
    }

    #[AsEventListener(event: PostBeforeDeletedEvent::class)]
    #[AsEventListener(event: PostBeforePurgeEvent::class)]
    public function sendApDeleteMessage(PostBeforeDeletedEvent|PostBeforePurgeEvent $event): void
    {
        if (!$event->post->apId) {
            $this->messageBus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->post, Uuid::v4()->toRfc4122()),
                    $event->post->user->getId(),
                    $event->post->magazine->getId()
                )
            );
        }
    }

    #[AsEventListener(event: PostCreatedEvent::class)]
    public function sendApCreateMessage(PostCreatedEvent $event): void
    {
        if (!$event->post->apId) {
            $this->messageBus->dispatch(new CreateMessage($event->post->getId(), \get_class($event->post)));
        } else {
            $this->handleMagazine($event->post);
        }
    }

    #[AsEventListener(event: PostEditedEvent::class)]
    public function sendApUpdateMessage(PostEditedEvent $event): void
    {
        if (!$event->post->apId) {
            $this->messageBus->dispatch(new UpdateMessage($event->post->getId(), \get_class($event->post)));
        }
    }

    private function handleMagazine(Post $post): void
    {
        if (!$post->tags) {
            return;
        }

        foreach ($post->tags as $tag) {
            if ($magazine = $this->magazineRepository->findOneByName($tag)) {
                ($this->postMagazineChange)($post, $magazine);

                return;
            }

            if ($magazine = $this->magazineRepository->findByTag($tag)) {
                ($this->postMagazineChange)($post, $magazine);

                return;
            }
        }
    }
}
