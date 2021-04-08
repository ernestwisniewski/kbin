<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\PostNotificationMessage;
use App\Service\NotificationManager;
use App\Repository\PostRepository;

class SentPostNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private PostRepository $postRepository,
        private NotificationManager $notificationManager
    ) {
    }

    public function __invoke(PostNotificationMessage $postNotificationMessage)
    {
        $post = $this->postRepository->find($postNotificationMessage->postId);
        if (!$post) {
            return;
        }

        $this->notificationManager->sendPostNotification($post);
    }
}
