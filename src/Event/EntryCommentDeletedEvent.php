<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\EntryComment;
use App\Entity\User;

class EntryCommentDeletedEvent
{
    public function __construct(public EntryComment $comment, public ?User $user = null)
    {
    }
}
