<?php

namespace App\Twig\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('post')]
final class PostComponent
{
    public Post $post;
    public bool $isSingle = false;
    public bool $showMagazineName = true;
    public bool $dateAsUrl = true;
    public bool $showCommentsPreview = false;
    public bool $showExpand = true;
    public bool $canSeeTrash = false;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    #[PostMount]
    public function postMount(array $attr): array
    {
        $this->canSeeTrashed();

        if ($this->isSingle) {
            $this->showMagazineName = false;

            if (isset($attr['class'])) {
                $attr['class'] = trim('post--single '.$attr['class']);
            } else {
                $attr['class'] = 'post--single';
            }
        }

        return $attr;
    }

    public function canSeeTrashed(): bool
    {
        if (VisibilityInterface::VISIBILITY_VISIBLE === $this->post->visibility) {
            return true;
        }

        if (VisibilityInterface::VISIBILITY_TRASHED === $this->post->visibility
            && $this->authorizationChecker->isGranted(
                'moderate',
                $this->post
            )
            && $this->canSeeTrash) {
            return true;
        }

        $this->post->image = null;

        return false;
    }
}
