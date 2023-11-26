<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use App\Kbin\MarkNewComment\MarkNewCommentCount;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('post', template: 'components/_cached.html.twig')]
final class PostComponent
{
    public Post $post;
    public bool $isSingle = false;
    public bool $showMagazineName = true;
    public bool $dateAsUrl = true;
    public bool $showCommentsPreview = false;
    public bool $showExpand = true;
    public bool $canSeeTrash = false;
    public int $newComments = 0;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly MarkNewCommentCount $markNewCommentCount,
        private readonly CacheInterface $cache,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly Security $security
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $key = $this->isSingle.'_'.$this->showMagazineName.'_'.$this->dateAsUrl.'_'.$this->showCommentsPreview.'_';
        $key .= $this->showExpand.'_'.$this->canSeeTrash.'_'.$this->post->getId().'_'.$this->security->getUser(
        )?->getId();
        $key .= $this->canSeeTrashed().'_'.$this->requestStack->getCurrentRequest()?->getLocale().'_';
        $key .= $this->requestStack->getCurrentRequest()->cookies->get(
            ThemeSettingsController::KBIN_POSTS_SHOW_PREVIEW
        ).'_';

        return $this->cache->get(
            'post_'.hash('sha256', $key),
            function (ItemInterface $item) use ($attributes) {
                $item->expiresAfter(900);

                $item->tag('post_'.$this->post->getId());
                $item->tag('user_view_'.$this->security->getUser()?->getId());

                return $this->twig->render(
                    'components/post.html.twig',
                    [
                        'attributes' => $attributes,
                        'post' => $this->post,
                        'isSingle' => $this->isSingle,
                        'showMagazineName' => $this->showMagazineName,
                        'showCommentsPreview' => $this->showCommentsPreview,
                        'dateAsUrl' => $this->dateAsUrl,
                        'showExpand' => $this->showExpand,
                        'canSeeTrashed' => $this->canSeeTrashed(),
                        'newComments' => $this->newComments,
                    ]
                );
            }
        );
    }

    #[PostMount]
    public function postMount(array $attr): array
    {
        $this->canSeeTrashed();
        $this->countNewComments();

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
        if (VisibilityInterface::VISIBILITY_VISIBLE === $this->post->getVisibility()) {
            return true;
        }

        if (VisibilityInterface::VISIBILITY_TRASHED === $this->post->getVisibility()
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

    private function countNewComments(): void
    {
        $user = $this->security->getUser();

        if (!$user || !$user->markNewComments) {
            return;
        }

        $this->newComments = ($this->markNewCommentCount)($user, $this->post);
    }
}
