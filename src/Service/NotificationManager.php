<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\EntryComment;
use App\Entity\PostComment;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Entry;
use App\Entity\Post;

class NotificationManager
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function sendEntryNotification(Entry $entry): void
    {

    }

    public function sendEntryCommentNotification(EntryComment $comment): void
    {

    }

    public function sendPostNotification(Post $post): void
    {

    }

    public function sendPostCommentNotification(PostComment $comment): void
    {

    }
}
