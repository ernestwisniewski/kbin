<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Repository\EntryRepository;
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

#[AsLiveComponent('related_entries', template: 'components/_cached.html.twig')]
final class RelatedEntriesComponent
{
    use DefaultActionTrait;

    public const TYPE_TAG = 'tag';
    public const TYPE_MAGAZINE = 'magazine';
    public const TYPE_RANDOM = 'random';

    public int $limit = 4;
    public ?string $tag = null;
    public ?string $magazine = null;
    public ?string $type = self::TYPE_RANDOM;
    public ?Entry $entry = null;
    public string $title = 'random_entries';

    #[LiveProp]
    public bool $refreshedRandom = false;

    public function __construct(
        private readonly EntryRepository $repository,
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
            $this->title = 'related_entries';
            $this->type = self::TYPE_TAG;
        }

        if ($this->magazine) {
            $this->title = 'related_entries';
            $this->type = self::TYPE_MAGAZINE;
        }

        return $attr;
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $entryId = $this->entry?->getId();
        $magazine = str_replace('@', '', $this->magazine ?? '');

        if ($this->refreshedRandom) {
            return $this->render($attributes);
        }

        return $this->cache->get(
            "related_entries_{$magazine}_{$this->tag}_{$entryId}_{$this->type}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
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
        $entries = match ($this->type) {
            self::TYPE_TAG => $this->repository->findRelatedByMagazine($this->tag, $this->limit + 20),
            self::TYPE_MAGAZINE => $this->repository->findRelatedByTag(
                $this->mentionManager->getUsername(explode('@', $this->magazine)[0]),
                $this->limit + 20
            ),
            default => $this->repository->findLast($this->limit + 150),
        };

        $entries = array_filter(
            $entries,
            fn (Entry $e) => !$e->isAdult
                && !$e->magazine->isAdult
                && VisibilityInterface::VISIBILITY_VISIBLE === $e->magazine->getVisibility()
        );

        if (\count($entries) > $this->limit) {
            shuffle($entries); // randomize the order
            $entries = \array_slice($entries, 0, $this->limit);
        }

        return $this->twig->render(
            'components/related_entries.html.twig',
            [
                'attributes' => $attributes,
                'entries' => $entries,
                'title' => $this->title,
                'type' => $this->type,
            ]
        );
    }
}
