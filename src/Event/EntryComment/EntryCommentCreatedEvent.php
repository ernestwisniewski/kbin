<?php declare(strict_types = 1);

namespace App\Event\EntryComment;

use App\Entity\EntryComment;

class EntryCommentCreatedEvent
{
    public function __construct(public EntryComment $comment)
    {
    }
}
