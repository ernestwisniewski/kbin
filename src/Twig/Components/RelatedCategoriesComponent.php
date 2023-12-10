<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Repository\CategoryRepository;
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

#[AsLiveComponent('related_categories', template: 'components/_cached.html.twig')]
final class RelatedCategoriesComponent
{
    use DefaultActionTrait;

    public const TYPE_RELATED = 'related';
    public const TYPE_RANDOM = 'random';

    public int $limit = 4;
    public ?string $tag = null;
    public ?string $magazine = null;
    public ?string $type = self::TYPE_RANDOM;
    public string $title = 'random_categories';

    #[LiveProp]
    public bool $refreshedRandom = false;

    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly CacheInterface $cache,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack
    ) {
    }

    #[PostMount]
    public function postMount(array $attr): array
    {
        if ($this->tag || $this->magazine) {
            $this->title = 'related_categories';
            $this->type = self::TYPE_RELATED;
        }

        return $attr;
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $magazine = str_replace('@', '', $this->magazine ?? '');

        if ($this->refreshedRandom) {
            return $this->render($attributes, $magazine);
        }

        return $this->cache->get(
            "related_categories_{$magazine}_{$this->tag}_{$this->type}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($attributes, $magazine) {
                $item->expiresAfter(60);

                return $this->render($attributes, $magazine);
            }
        );
    }

    #[LiveAction]
    public function refreshRandom(): void
    {
        $this->refreshedRandom = true;
    }

    private function render(ComponentAttributes $attributes, string $magazine): string
    {
        $magazines = match ($this->type) {
            self::TYPE_RELATED => $this->repository->findRelated($magazine),
            default => $this->repository->findRandom(),
        };

        return $this->twig->render(
            'components/related_categories.html.twig',
            [
                'attributes' => $attributes,
                'categories' => $magazines,
                'title' => $this->title,
                'type' => $this->type,
            ]
        );
    }
}
