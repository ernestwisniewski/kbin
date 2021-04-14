<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PostNotificationMessage;
use App\Repository\PostRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentPostNotificationHandler implements MessageHandlerInterface
{
    public function __construct(
        private PostRepository $repository,
        private NotificationManager $manager
    ) {
    }

    public function __invoke(PostNotificationMessage $message)
    {
        $post = $this->repository->find($message->postId);
        if (!$post) {
            return;
        }

        $this->manager->sendPostNotification($post);
    }
}
