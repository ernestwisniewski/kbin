<?php

declare(strict_types=1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\EntryCommentDeletedNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class SentEntryCommentDeletedNotificationHandler
{
    public function __construct(
        private readonly EntryCommentRepository $repository,
        private readonly NotificationManager $manager
    ) {
    }

    public function __invoke(EntryCommentDeletedNotificationMessage $message)
    {
        $comment = $this->repository->find($message->commentId);

        if (!$comment) {
            throw new UnrecoverableMessageHandlingException('Comment not found');
        }

        $this->manager->sendDeleted($comment);
    }
}
