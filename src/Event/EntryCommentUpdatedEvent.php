<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\EntryComment;

class EntryCommentUpdatedEvent
{
    protected EntryComment $comment;

    public function __construct(EntryComment $comment)
    {
        $this->comment = $comment;
    }

    public function getComment(): EntryComment
    {
        return $this->comment;
    }
}
