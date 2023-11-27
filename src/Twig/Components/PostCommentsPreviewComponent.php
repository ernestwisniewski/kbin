<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Post;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('post_comments_preview', template: 'components/_cached.html.twig')]
final class PostCommentsPreviewComponent
{
    public Post $post;

    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $postId = $this->post->getId();
        $userId = $this->security->getUser()?->getId();

        return $this->cache->get(
            "post_comment_preview_{$postId}_{$userId}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($postId, $userId, $attributes) {
                $item->expiresAfter(300);
                $item->tag(['post_comments_user_'.$userId]);
                $item->tag(['post_'.$postId]);
                $item->tag(['user_view_'.$userId]);

                return $this->twig->render(
                    'components/post_comments_preview.html.twig',
                    [
                        'attributes' => new ComponentAttributes($attributes->all()),
                        'post' => $this->post,
                        'comments' => $this->post->lastActive < (new \DateTime('-4 hours'))
                            ? $this->post->getBestComments($this->security->getUser())
                            : $this->post->getLastComments($this->security->getUser()),
                    ]
                );
            }
        );
    }
}
