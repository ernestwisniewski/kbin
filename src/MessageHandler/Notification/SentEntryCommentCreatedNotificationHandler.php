<?php declare(strict_types = 1);

namespace App\MessageHandler\Notification;

use App\Message\Notification\EntryCommentCreatedNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentEntryCommentCreatedNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryCommentRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(EntryCommentCreatedNotificationMessage $message)
    {
        $comment = $this->repository->find($message->commentId);

        if (!$comment) {
            throw new UnrecoverableMessageHandlingException('Comment not found');
        }

        $this->manager->sendCreated($comment);
    }
}
