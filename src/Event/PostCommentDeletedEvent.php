<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\PostComment;
use App\Entity\User;

class PostCommentDeletedEvent
{
    public function __construct(private PostComment $comment, private ?User $user = null)
    {
    }

    public function getComment(): PostComment
    {
        return $this->comment;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
