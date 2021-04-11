<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryCommentNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentEntryCommentNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntryCommentRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(EntryCommentNotificationMessage $entryCreatedMessage)
    {
        $comment = $this->repository->find($entryCreatedMessage->commentId);
        if (!$comment) {
            return;
        }

        $this->manager->sendEntryCommentNotification($comment);
    }
}
