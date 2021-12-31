<?php declare(strict_types=1);

namespace App\Event\PostComment;

use App\Entity\PostComment;
use App\Entity\User;

class PostCommentRestoredEvent
{
    public function __construct(public PostComment $comment, public ?User $user = null)
    {
    }
}
