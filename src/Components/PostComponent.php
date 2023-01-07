<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post')]
class PostComponent
{
    public Post $post;
    public ?string $extraClass = null;
    public int $index = 1;
    public bool $showMagazine = true;
    public bool $showAllComments = false;
    public bool $showBestComments = false;
    public bool $canSeeTrash = false;

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function canSeeTrashed(): bool
    {
        if (VisibilityInterface::VISIBILITY_VISIBLE === $this->post->visibility) {
            return true;
        }

        if (VisibilityInterface::VISIBILITY_TRASHED === $this->post->visibility
            && $this->authorizationChecker->isGranted(
                'moderate',
                $this->post->magazine
            )
            && $this->canSeeTrash) {
            return true;
        }

        return false;
    }
}
