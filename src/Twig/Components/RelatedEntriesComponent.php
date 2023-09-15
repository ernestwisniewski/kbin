<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Entry;
use App\Repository\EntryRepository;
use App\Service\MentionManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('related_entries', template: 'components/_cached.html.twig')]
final class RelatedEntriesComponent
{
    public const TYPE_TAG = 'tag';
    public const TYPE_MAGAZINE = 'magazine';
    public const TYPE_RANDOM = 'random';

    public int $limit = 4;
    public ?string $tag = null;
    public ?string $magazine = null;
    public ?string $type = self::TYPE_RANDOM;
    public ?Entry $entry = null;
    public string $title = 'random_entries';

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

        return $this->cache->get(
            "related_entries_{$magazine}_{$this->tag}_{$entryId}_{$this->type}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($attributes) {
                $item->expiresAfter(60);

                $entries = match ($this->type) {
                    self::TYPE_TAG => $this->repository->findRelatedByMagazine($this->tag, $this->limit + 20),
                    self::TYPE_MAGAZINE => $this->repository->findRelatedByTag(
                        $this->mentionManager->getUsername($this->magazine),
                        $this->limit + 20
                    ),
                    default => $this->repository->findLast($this->limit + 150),
                };

                $entries = array_filter($entries, fn (Entry $e) => !$e->isAdult && !$e->magazine->isAdult);

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
                    ]
                );
            }
        );
    }
}
