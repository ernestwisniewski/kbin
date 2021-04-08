<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\EntryComment;

class EntryCommentUpdatedEvent
{
    public function __construct(public EntryComment $comment)
    {
    }
}
