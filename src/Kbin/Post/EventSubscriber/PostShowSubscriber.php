<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post\EventSubscriber;

use App\Entity\Notification;
use App\Kbin\Post\EventSubscriber\Event\PostHasBeenSeenEvent;
use App\Repository\NotificationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class PostShowSubscriber
{
    public function __construct(
        private Security $security,
        private NotificationRepository $notificationRepository,
    ) {
    }

    #[AsEventListener(event: PostHasBeenSeenEvent::class)]
    public function readNotifications(PostHasBeenSeenEvent $event): void
    {
        if (!$this->security->getUser()) {
            return;
        }

        $notifications = $this->notificationRepository->findUnreadPostNotifications(
            $this->security->getUser(),
            $event->post
        );

        if (!\count($notifications)) {
            return;
        }

        array_map(fn ($notification) => $notification->status = Notification::STATUS_READ, $notifications);
    }
}
