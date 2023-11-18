<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

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

class NotificationManagerTypeResolver
{
    public function __construct(
        private readonly EntryNotificationManager $entryNotificationManager,
        private readonly EntryCommentNotificationManager $entryCommentNotificationManager,
        private readonly PostNotificationManager $postNotificationManager,
        private readonly PostCommentNotificationManager $postCommentNotificationManager,
    ) {
    }

    public function resolve(ContentInterface $subject): ContentNotificationManagerInterface
    {
        return match (true) {
            $subject instanceof Entry => $this->entryNotificationManager,
            $subject instanceof EntryComment => $this->entryCommentNotificationManager,
            $subject instanceof Post => $this->postNotificationManager,
            $subject instanceof PostComment => $this->postCommentNotificationManager,
            default => throw new \LogicException(),
        };
    }
}
