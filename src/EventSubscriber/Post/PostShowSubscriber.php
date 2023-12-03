<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Entity\Notification;
use App\Entity\Post;
use App\Kbin\Post\EventSubscriber\Event\PostHasBeenSeenEvent;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostShowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly NotificationRepository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[ArrayShape([PostHasBeenSeenEvent::class => 'string'])]
    public static function getSubscribedEvents(): array
    {
        return [
            PostHasBeenSeenEvent::class => 'onShowEntry',
        ];
    }

    public function onShowEntry(PostHasBeenSeenEvent $event): void
    {
        $this->readMessage($event->post);
    }

    private function readMessage(Post $post): void
    {
        if (!$this->security->getUser()) {
            return;
        }

        $notifications = $this->repository->findUnreadPostNotifications($this->security->getUser(), $post);

        if (!$notifications) {
            return;
        }

        array_map(fn ($notification) => $notification->status = Notification::STATUS_READ, $notifications);

        $this->entityManager->flush();
    }
}
