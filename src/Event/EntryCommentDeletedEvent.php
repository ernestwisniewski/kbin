<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\User;

class EntryCommentDeletedEvent
{
    private EntryComment $comment;
    private ?User $user;

    public function __construct(EntryComment $comment, ?User $user = null)
    {
        $this->comment = $comment;
        $this->user    = $user;
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
