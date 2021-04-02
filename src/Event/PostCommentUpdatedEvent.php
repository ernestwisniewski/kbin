<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\PostComment;

class PostCommentUpdatedEvent
{
    public function __construct(private PostComment $comment)
    {
    }

    public function getComment(): PostComment
    {
        return $this->comment;
    }
}
