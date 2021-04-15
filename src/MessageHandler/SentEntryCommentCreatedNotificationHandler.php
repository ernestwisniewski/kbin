<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryCommentCreatedNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Service\NotificationManager;
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
            return;
        }

        $this->manager->sendEntryCommentNotification($comment);
    }
}
