<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\PostComment;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_comment')]
class PostCommentComponent
{
    public PostComment $comment;
    public bool $withParent = false;
    public ?string $extraClass = null;
    public bool $showMagazine = true;
    public bool $canSeeTrash = false;

    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function canSeeTrashed(): bool
    {
        if ($this->comment->visibility === VisibilityInterface::VISIBILITY_VISIBLE) {
            return true;
        }

        if ($this->comment->visibility === VisibilityInterface::VISIBILITY_TRASHED
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
