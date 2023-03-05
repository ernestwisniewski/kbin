<?php

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\PostComment;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_comment')]
final class PostCommentComponent
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public PostComment $comment;
    public bool $dateAsUrl = true;
    public bool $showNested = false;
    public int $level = 1;

    public function getLevel(): int
    {
        if (ThemeSettingsController::CLASSIC === $this->requestStack->getMainRequest()->cookies->get(
            ThemeSettingsController::POST_COMMENTS_VIEW
        )) {
            return min($this->level, 2);
        }

        return min($this->level, 10);
    }
}
