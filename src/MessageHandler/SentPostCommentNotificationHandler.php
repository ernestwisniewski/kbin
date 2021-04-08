<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\PostCommentNotificationMessage;
use App\Repository\PostCommentRepository;
use App\Service\NotificationManager;

class SentPostCommentNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private PostCommentRepository $commentRepository,
        private NotificationManager $notificationManager
    ) {
    }

    public function __invoke(PostCommentNotificationMessage $entryCreatedMessage)
    {
        $comment = $this->commentRepository->find($entryCreatedMessage->commentId);
        if (!$comment) {
            return;
        }

        $this->notificationManager->sendPostCommentNotification($comment);
    }
}
