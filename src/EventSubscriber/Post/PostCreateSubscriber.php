<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Entity\Post;
use App\Event\Post\PostCreatedEvent;
use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Kbin\Post\PostMagazineChange;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\Notification\PostCreatedNotificationMessage;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PostCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly MagazineRepository $magazineRepository,
        private readonly PostRepository $postRepository,
        private readonly PostMagazineChange $postMagazineChange,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCreatedEvent::class => 'onPostCreated',
        ];
    }

    public function onPostCreated(PostCreatedEvent $event): void
    {
        $this->cache->invalidateTags(['user_'.$event->post->user->getId()]);

        $event->post->magazine->postCount = $this->postRepository->countPostsByMagazine($event->post->magazine);

        $this->entityManager->flush();

        if (!$event->post->apId) {
            $this->bus->dispatch(new CreateMessage($event->post->getId(), \get_class($event->post)));
        } else {
            $this->handleMagazine($event->post);
        }

        $this->bus->dispatch(new PostCreatedNotificationMessage($event->post->getId()));
        if ($event->post->body) {
            $this->bus->dispatch(new LinkEmbedMessage($event->post->body));
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
                break;
            }

            if ($magazine = $this->magazineRepository->findByTag($tag)) {
                ($this->postMagazineChange)($post, $magazine);
                break;
            }
        }
    }
}
