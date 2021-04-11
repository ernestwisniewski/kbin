<?php declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\PostNotificationMessage;
use App\Service\NotificationManager;
use App\Repository\PostRepository;

class SentPostNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private PostRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(PostNotificationMessage $postNotificationMessage)
    {
        $post = $this->repository->find($postNotificationMessage->postId);
        if (!$post) {
            return;
        }

        $this->manager->sendPostNotification($post);
    }
}
