<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntryNotificationMessage;
use App\Message\PostNotificationMessage;
use App\Repository\EntryRepository;
use App\Repository\PostRepository;
use App\Service\NotificationManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SentPostNotificationHandler implements MessageHandlerInterface
{
    private PostRepository $postRepository;
    private NotificationManager $notificationManager;

    public function __construct(PostRepository $postRepository, NotificationManager $notificationManager)
    {
        $this->postRepository      = $postRepository;
        $this->notificationManager = $notificationManager;
    }

    public function __invoke(PostNotificationMessage $postNotificationMessage)
    {
        $post = $this->postRepository->find($postNotificationMessage->getPostId());
        if (!$post) {
            return;
        }

        $this->notificationManager->sendPostNotification($post);
    }
}
