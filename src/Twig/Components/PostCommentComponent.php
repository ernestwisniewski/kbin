<?php

namespace App\Twig\Components;

use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_comment')]
final class PostCommentComponent
{
    public PostComment $comment;
    public bool $dateAsUrl = true;
    public bool $showNested = false;
    public int $level = 1;

    public function getLevel(): int
    {
        return min($this->level, 10);
    }
}
