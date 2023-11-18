<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\PostCreatedNotificationMessage;
use App\Repository\PostRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class SentPostCreatedNotificationHandler
{
    public function __construct(
        private readonly PostRepository $repository,
        private readonly NotificationManager $manager
    ) {
    }

    public function __invoke(PostCreatedNotificationMessage $message)
    {
        $post = $this->repository->find($message->postId);

        if (!$post) {
            throw new UnrecoverableMessageHandlingException('Post not found');
        }

        $this->manager->sendCreated($post);
    }
}
