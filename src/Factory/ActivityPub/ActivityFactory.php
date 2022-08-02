<?php declare(strict_types=1);

namespace App\Factory\ActivityPub;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;

class ActivityFactory
{
    public function __construct(
        private EntryPageFactory $pageFactory,
        private EntryCommentNoteFactory $entryNoteFactory,
        private PostNoteFactory $postNoteFactory,
        private PostCommentNoteFactory $postCommentNoteFactory
    ) {

    }

    public function create(ActivityPubActivityInterface $activity): array
    {
        return match (true) {
            $activity instanceof Entry => $this->pageFactory->create($activity),
            $activity instanceof EntryComment => $this->entryNoteFactory->create($activity),
            $activity instanceof Post => $this->postNoteFactory->create($activity),
            $activity instanceof PostComment => $this->postCommentNoteFactory->create($activity),
            default => throw new \LogicException(),
        };
    }
}
