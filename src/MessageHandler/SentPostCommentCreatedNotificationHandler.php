<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PostCommentCreatedNotificationMessage;
use App\Repository\PostCommentRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentPostCommentCreatedNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private PostCommentRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(PostCommentCreatedNotificationMessage $message)
    {
        $comment = $this->repository->find($message->commentId);
        if (!$comment) {
            return;
        }

        $this->manager->sendPostCommentCreatedNotification($comment);
    }
}
