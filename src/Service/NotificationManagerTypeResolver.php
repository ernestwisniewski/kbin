<?php declare(strict_types = 1);

namespace App\Service;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\Contracts\ContentNotificationManagerInterface;
use App\Service\Notification\EntryCommentNotificationManager;
use App\Service\Notification\EntryNotificationManager;
use App\Service\Notification\PostCommentNotificationManager;
use App\Service\Notification\PostNotificationManager;
use LogicException;

class NotificationManagerTypeResolver
{
    public function __construct(
        private EntryNotificationManager $entryNotificationManager,
        private EntryCommentNotificationManager $entryCommentNotificationManager,
        private PostNotificationManager $postNotificationManager,
        private PostCommentNotificationManager $postCommentNotificationManager,
    ) {
    }

    public function resolve(ContentInterface $subject): ContentNotificationManagerInterface
    {
        return match (true) {
            $subject instanceof Entry => $this->entryNotificationManager,
            $subject instanceof EntryComment => $this->entryCommentNotificationManager,
            $subject instanceof Post => $this->postNotificationManager,
            $subject instanceof PostComment => $this->postCommentNotificationManager,
            default => throw new LogicException(),
        };
    }

}
