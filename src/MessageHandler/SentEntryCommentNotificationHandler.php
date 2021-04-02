<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryCommentNotificationMessage;
use App\Message\EntryNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class SentEntryCommentNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryCommentRepository $commentRepository,
        private NotificationManager $notificationManager
    ) {
    }

    public function __invoke(EntryCommentNotificationMessage $entryCreatedMessage)
    {
        $comment = $this->commentRepository->find($entryCreatedMessage->getCommentId());
        if (!$comment) {
            return;
        }

        $this->notificationManager->sendEntryCommentNotification($comment);
    }
}
