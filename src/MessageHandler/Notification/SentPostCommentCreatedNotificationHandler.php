<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\PostCommentCreatedNotificationMessage;
use App\Repository\PostCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class SentPostCommentCreatedNotificationHandler
{
    public function __construct(
        private readonly PostCommentRepository $repository,
        private readonly NotificationManager $manager
    ) {
    }

    public function __invoke(PostCommentCreatedNotificationMessage $message)
    {
        $comment = $this->repository->find($message->commentId);

        if (!$comment) {
            throw new UnrecoverableMessageHandlingException('Comment not found');
        }

        $this->manager->sendCreated($comment);
    }
}
