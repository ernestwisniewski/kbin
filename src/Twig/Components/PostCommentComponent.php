<?php

namespace App\Twig\Components;

use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_comment')]
final class PostCommentComponent
{
    public PostComment $comment;
}
