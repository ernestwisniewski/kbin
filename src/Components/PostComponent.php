<?php declare(strict_types=1);

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
    public bool $showMagazine = true;
    public bool $showAllComments = false;
    public bool $showBestComments = false;
    public bool $canSeeTrash = false;

    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function canSeeTrashed(): bool
    {
        if ($this->post->visibility === VisibilityInterface::VISIBILITY_VISIBLE) {
            return true;
        }

        if ($this->post->visibility === VisibilityInterface::VISIBILITY_TRASHED
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
