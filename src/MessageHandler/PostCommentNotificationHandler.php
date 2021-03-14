<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryCommentNotificationMessage;
use App\Message\EntryNotificationMessage;
use App\Message\PostCommentNotificationMessage;
use App\Repository\EntryCommentRepository;
use App\Repository\PostCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PostCommentNotificationHandler implements MessageHandlerInterface
{
    private PostCommentRepository $commentRepository;
    private NotificationManager $notificationManager;

    public function __construct(PostCommentRepository $commentRepository, NotificationManager $notificationManager)
    {
        $this->commentRepository   = $commentRepository;
        $this->notificationManager = $notificationManager;
    }

    public function __invoke(PostCommentNotificationMessage $entryCreatedMessage)
    {
        $comment = $this->commentRepository->find($entryCreatedMessage->getCommentId());
        if (!$comment) {
            return;
        }

        $this->notificationManager->sendPostCommentNotification($comment);
    }
}
