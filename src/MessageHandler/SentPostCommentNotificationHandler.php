<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\PostCommentNotificationMessage;
use App\Repository\PostCommentRepository;
use App\Service\NotificationManager;

class SentPostCommentNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private PostCommentRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(PostCommentNotificationMessage $entryCreatedMessage)
    {
        $comment = $this->repository->find($entryCreatedMessage->commentId);
        if (!$comment) {
            return;
        }

        $this->manager->sendPostCommentNotification($comment);
    }
}
