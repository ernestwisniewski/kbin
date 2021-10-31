<?php declare(strict_types = 1);

namespace App\Event\PostComment;

use App\Entity\Magazine;

class PostCommentPurgedEvent
{
    public function __construct(public Magazine $magazine)
    {
    }
}
