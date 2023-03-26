<?php

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\EntryComment;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment')]
final class EntryCommentComponent
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public EntryComment $comment;
    public bool $showMagazineName = true;
    public bool $showEntryTitle = true;
    public bool $showNested = false;
    public int $level = 1;
    public bool $showModeratePanel = false;

    public function getLevel(): int
    {
        if (ThemeSettingsController::CLASSIC === $this->requestStack->getMainRequest()->cookies->get(
            ThemeSettingsController::ENTRY_COMMENTS_VIEW
        )) {
            return min($this->level, 2);
        }

        return min($this->level, 10);
    }
}
