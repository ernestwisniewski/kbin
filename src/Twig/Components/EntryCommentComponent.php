<?php

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\EntryComment;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment')]
final class EntryCommentComponent
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public EntryComment $comment;
    public bool $showMagazineName = true;
    public bool $showEntryTitle = true;
    public bool $showNested = false;
    public int $level = 1;
    public bool $canSeeTrash = false;
    public bool $dateAsUrl = false;

    public function postMount(array $attr): array
    {
        $this->canSeeTrashed();

        return $attr;
    }

    public function getLevel(): int
    {
        if (ThemeSettingsController::CLASSIC === $this->requestStack->getMainRequest()->cookies->get(
                ThemeSettingsController::ENTRY_COMMENTS_VIEW
            )) {
            return min($this->level, 2);
        }

        return min($this->level, 10);
    }

    public function canSeeTrashed(): bool
    {
        if ($this->comment->visibility === VisibilityInterface::VISIBILITY_VISIBLE) {
            return true;
        }

        if ($this->comment->visibility === VisibilityInterface::VISIBILITY_TRASHED
            && $this->authorizationChecker->isGranted(
                'moderate',
                $this->comment
            )
            && $this->canSeeTrash) {
            return true;
        }

        $this->comment->image = null;

        return false;
    }
}
