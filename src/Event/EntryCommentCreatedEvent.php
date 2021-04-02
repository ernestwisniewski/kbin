<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\EntryComment;

class EntryCommentCreatedEvent
{
    public function __construct(private EntryComment $comment)
    {
    }

    public function getComment(): EntryComment
    {
        return $this->comment;
    }
}
