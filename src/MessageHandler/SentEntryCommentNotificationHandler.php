<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\EntryCommentNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Service\NotificationManager;

class SentEntryCommentNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryCommentRepository $commentRepository,
        private NotificationManager $notificationManager
    ) {
    }

    public function __invoke(EntryCommentNotificationMessage $entryCreatedMessage)
    {
        $comment = $this->commentRepository->find($entryCreatedMessage->commentId);
        if (!$comment) {
            return;
        }

        $this->notificationManager->sendEntryCommentNotification($comment);
    }
}
