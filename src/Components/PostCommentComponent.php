<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\PostComment;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_comment')]
class PostCommentComponent
{
    public PostComment $comment;
    public bool $withParent = false;
    public ?string $extraClass = null;
    public bool $showMagazine = true;
}
