<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\PostComment;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_comment')]
final class PostCommentComponent
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public PostComment $comment;
    public bool $dateAsUrl = true;
    public bool $showNested = false;
    public bool $withPost = false;
    public int $level = 1;
    public bool $canSeeTrash = false;

    public function postMount(array $attr): array
    {
        $this->canSeeTrashed();

        return $attr;
    }

    public function getLevel(): int
    {
        if (ThemeSettingsController::CLASSIC === $this->requestStack->getMainRequest()->cookies->get(
            ThemeSettingsController::POST_COMMENTS_VIEW
        )) {
            return min($this->level, 2);
        }

        return min($this->level, 10);
    }

    public function canSeeTrashed(): bool
    {
        if (VisibilityInterface::VISIBILITY_VISIBLE === $this->comment->visibility) {
            return true;
        }

        if (VisibilityInterface::VISIBILITY_TRASHED === $this->comment->visibility
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
