<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\EntryComment;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment')]
class EntryCommentComponent
{
    public EntryComment $comment;
    public ?string $extraClass = null;
    public int $level = 1;
    public bool $withParent = false;
    public bool $nested = true;
    public bool $showMagazine = false;
    public bool $canSeeTrash = false;

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function canSeeTrashed(): bool
    {
        if (VisibilityInterface::VISIBILITY_VISIBLE === $this->comment->visibility) {
            return true;
        }

        if (VisibilityInterface::VISIBILITY_TRASHED === $this->comment->visibility
            && $this->authorizationChecker->isGranted(
                'moderate',
                $this->comment->magazine
            )
            && $this->canSeeTrash) {
            return true;
        }

        return false;
    }
}
