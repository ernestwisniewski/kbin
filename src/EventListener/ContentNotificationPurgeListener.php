<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\Notification\EntryCommentNotificationManager;
use App\Service\Notification\EntryNotificationManager;
use App\Service\Notification\PostCommentNotificationManager;
use App\Service\Notification\PostNotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ContentNotificationPurgeListener
{
    public function __construct(
        private EntryNotificationManager $entryManager,
        private EntryCommentNotificationManager $entryCommentManager,
        private PostNotificationManager $postManager,
        private PostCommentNotificationManager $postCommentManager,
    ) {
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        switch ($object) {
            case $object instanceof Entry:
                $this->entryManager->purgeNotifications($object);
                break;
            case $object instanceof EntryComment:
                $this->entryCommentManager->purgeNotifications($object);
                break;
            case $object instanceof Post:
                $this->postManager->purgeNotifications($object);
                break;
            case $object instanceof PostComment:
                $this->postCommentManager->purgeNotifications($object);
                break;
        }
    }
}
