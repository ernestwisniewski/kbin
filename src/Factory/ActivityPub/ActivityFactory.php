<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;

class ActivityFactory
{
    public function __construct(
        private readonly EntryPageFactory $pageFactory,
        private readonly EntryCommentNoteFactory $entryNoteFactory,
        private readonly PostNoteFactory $postNoteFactory,
        private readonly PostCommentNoteFactory $postCommentNoteFactory
    ) {
    }

    public function create(ActivityPubActivityInterface $activity, bool $context = false): array
    {
        return match (true) {
            $activity instanceof Entry => $this->pageFactory->create($activity, $context),
            $activity instanceof EntryComment => $this->entryNoteFactory->create($activity, $context),
            $activity instanceof Post => $this->postNoteFactory->create($activity, $context),
            $activity instanceof PostComment => $this->postCommentNoteFactory->create($activity, $context),
            default => throw new \LogicException(),
        };
    }
}
