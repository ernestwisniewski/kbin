<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\PostComment;
use App\Entity\User;

class PostCommentDeletedEvent
{
    public function __construct(public PostComment $comment, public ?User $user = null)
    {
    }
}
