<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Controller\User\ThemeSettingsController;
use App\Entity\PostComment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('post_comments_nested', template: 'components/_cached.html.twig')]
final class PostCommentsNestedComponent
{
    public PostComment $comment;
    public int $level;
    public string $view = ThemeSettingsController::TREE;

    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $comment = $this->comment->root ?? $this->comment;
        $commentId = $comment->getId();
        $postId = $comment->post->getId();
        $userId = $this->security->getUser()?->getId();

        return $this->cache->get(
            "post_comments_nested_{$commentId}_{$userId}_{$this->view}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($commentId, $userId, $postId) {
                $item->expiresAfter(3600);
                $item->tag(['post_comments_user_'.$userId]);
                $item->tag(['post_comment_'.$commentId]);
                $item->tag(['post_'.$postId]);
                $item->tag(['user_view_'.$userId]);

                return $this->twig->render(
                    'components/post_comments_nested.html.twig',
                    [
                        'comment' => $this->comment,
                        'level' => $this->level,
                        'view' => $this->view,
                    ]
                );
            }
        );
    }
}
