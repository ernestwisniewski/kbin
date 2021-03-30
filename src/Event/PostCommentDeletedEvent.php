<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\PostComment;
use App\Entity\User;

class PostCommentDeletedEvent
{
    private PostComment $comment;
    private ?User $user;

    public function __construct(PostComment $comment, ?User $user = null)
    {
        $this->comment = $comment;
        $this->user    = $user;
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
