<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EventListener;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\Notification\EntryCommentNotificationManager;
use App\Service\Notification\EntryNotificationManager;
use App\Service\Notification\PostCommentNotificationManager;
use App\Service\Notification\PostNotificationManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;

readonly class ContentNotificationPurgeListener
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
                $this->entryManager->purgeMagazineLog($object);
                break;
            case $object instanceof EntryComment:
                $this->entryCommentManager->purgeNotifications($object);
                $this->entryCommentManager->purgeMagazineLog($object);
                break;
            case $object instanceof Post:
                $this->postManager->purgeNotifications($object);
                $this->postManager->purgeMagazineLog($object);
                break;
            case $object instanceof PostComment:
                $this->postCommentManager->purgeNotifications($object);
                $this->postCommentManager->purgeMagazineLog($object);
                break;
        }
    }
}
