<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Message;
use App\Entity\MessageNotification;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Service\Notification\EntryCommentNotificationManager;
use App\Service\Notification\EntryNotificationManager;
use App\Service\Notification\MessageNotificationManager;
use App\Service\Notification\PostCommentNotificationManager;
use App\Service\Notification\PostNotificationManager;
use Doctrine\ORM\EntityManagerInterface;

class NotificationManager
{
    public function __construct(
        private EntryNotificationManager $entryNotificationManager,
        private EntryCommentNotificationManager $entryCommentNotificationManager,
        private PostNotificationManager $postNotificationManager,
        private PostCommentNotificationManager $postCommentNotificationManager,
        private MessageNotificationManager $messageNotificationManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendNewEntryNotification(Entry $entry): void
    {
        $this->entryNotificationManager->send($entry);
    }

    public function sendEntryCommentNotification(EntryComment $comment): void
    {
        $this->entryCommentNotificationManager->send($comment);
    }

    public function sendPostNotification(Post $post): void
    {
        $this->postNotificationManager->send($post);
    }

    public function sendPostCommentNotification(PostComment $comment): void
    {
        $this->postCommentNotificationManager->send($comment);
    }

    public function sendMessageNotification(Message $message, User $sender): void
    {
        $this->messageNotificationManager->send($message, $sender);
    }

    public function markAllAsRead(User $user): void
    {
        $notifications = $user->getNewNotifications();

        foreach ($notifications as $notification) {
            $notification->status = Notification::STATUS_READ;
        }

        $this->entityManager->flush();
    }

    public function clear(User $user): void
    {
        $notifications = $user->notifications;

        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();
    }


    public function readMessageNotification(Message $message, User $user): void
    {
        $repo = $this->entityManager->getRepository(MessageNotification::class);

        $notifications = $repo->findBy(
            [
                'message' => $message,
                'user'    => $user,
            ]
        );

        foreach ($notifications as $notification) {
            $notification->status = Notification::STATUS_READ;
        }

        $this->entityManager->flush();
    }
}
