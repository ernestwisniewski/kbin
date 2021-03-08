<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\PostComment;

class PostCommentBeforePurgeEvent
{
    protected PostComment $comment;

    public function __construct(PostComment $comment)
    {
        $this->comment = $comment;
    }

    public function getComment(): PostComment
    {
        return $this->comment;
    }
}
