<?php declare(strict_types=1);

namespace App\Event\EntryComment;

use App\Entity\EntryComment;
use App\Entity\User;

class EntryCommentRestoredEvent
{
    public function __construct(public EntryComment $comment, public ?User $user = null)
    {
    }
}
