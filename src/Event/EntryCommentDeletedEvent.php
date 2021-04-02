<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\User;

class EntryCommentDeletedEvent
{
    public function __construct(private EntryComment $comment, private ?User $user = null)
    {
    }

    public function getComment(): EntryComment
    {
        return $this->comment;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
