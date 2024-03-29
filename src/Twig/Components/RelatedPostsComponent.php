<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\MentionManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsLiveComponent('related_posts', template: 'components/_cached.html.twig')]
final class RelatedPostsComponent
{
    use DefaultActionTrait;

    public const TYPE_TAG = 'tag';
    public const TYPE_MAGAZINE = 'magazine';
    public const TYPE_RANDOM = 'random';

    public int $limit = 4;
    public ?string $tag = null;
    public ?string $magazine = null;
    public ?string $type = self::TYPE_RANDOM;
    public ?Post $post = null;
    public string $title = 'random_posts';

    #[LiveProp]
    public bool $refreshedRandom = false;

    public function __construct(
        private readonly PostRepository $repository,
        private readonly CacheInterface $cache,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly MentionManager $mentionManager
    ) {
    }

    #[PostMount]
    public function postMount(array $attr): array
    {
        if ($this->tag) {
            $this->title = 'related_posts';
            $this->type = self::TYPE_TAG;
        }

        if ($this->magazine) {
            $this->title = 'related_posts';
            $this->type = self::TYPE_MAGAZINE;
        }

        return $attr;
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $postId = $this->post?->getId();
        $magazine = str_replace('@', '', $this->magazine ?? '');

        if ($this->refreshedRandom) {
            return $this->render($attributes);
        }

        return $this->cache->get(
            "related_posts_{$magazine}_{$this->tag}_{$postId}_{$this->type}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($attributes) {
                $item->expiresAfter(300);

                return $this->render($attributes);
            }
        );
    }

    #[LiveAction]
    public function refreshRandom(): void
    {
        $this->refreshedRandom = true;
    }

    private function render(ComponentAttributes $attributes): string
    {
        $posts = match ($this->type) {
            self::TYPE_TAG => $this->repository->findRelatedByMagazine($this->tag, $this->limit + 20),
            self::TYPE_MAGAZINE => $this->repository->findRelatedByTag(
                $this->mentionManager->getUsername(explode('@', $this->magazine)[0]),
                $this->limit + 20
            ),
            default => $this->repository->findLast($this->limit + 150),
        };

        $posts = array_filter($posts, fn (Post $p) => !$p->isAdult
            && !$p->magazine->isAdult
            && VisibilityInterface::VISIBILITY_VISIBLE === $p->magazine->getVisibility()
        );

        if (\count($posts) > $this->limit) {
            shuffle($posts); // randomize the order
            $posts = \array_slice($posts, 0, $this->limit);
        }

        return $this->twig->render(
            'components/related_posts.html.twig',
            [
                'attributes' => $attributes,
                'posts' => $posts,
                'title' => $this->title,
                'type' => $this->type,
            ]
        );
    }
}
