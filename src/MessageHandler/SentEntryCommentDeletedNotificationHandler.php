<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryCommentDeletedNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentEntryCommentDeletedNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryCommentRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(EntryCommentDeletedNotificationMessage $message)
    {
        $comment = $this->repository->find($message->commentId);
        if (!$comment) {
            return;
        }

        $this->manager->sendEntryCommentDeletedNotification($comment);
    }
}
