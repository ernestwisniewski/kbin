<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Entry;
use App\Repository\EntryRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('related_entries_sidebar')]
class RelatedEntriesSidebarComponent
{
    public const RELATED_LIMIT = 4;
    public const TYPE_TAG = 'tag';
    public const TYPE_MAGAZINE = 'magazine';

    public string $tag = '';
    public string $type = self::TYPE_TAG;
    public ?Entry $entry = null;

    public function __construct(
        private readonly EntryRepository $repository,
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly CacheInterface $cache
    ) {
    }

    public function getHtml(): string
    {
        return $this->cache->get(
            'related_entries_sidebar_'.$this->type.'_'.str_replace('@', '-', $this->tag).'_'.$this->security->getUser(
            )?->getId(),
            function (ItemInterface $item) {
                $item->expiresAfter(60);

                $entries = match ($this->type) {
                    self::TYPE_TAG => $this->repository->findRelatedByTag($this->tag, self::RELATED_LIMIT + 20),
                    self::TYPE_MAGAZINE => $this->repository->findRelatedByMagazine(
                        $this->tag,
                        self::RELATED_LIMIT + 20
                    ),
                    default => $this->repository->findLast(self::RELATED_LIMIT + 20),
                };

                if ($this->entry) {
                    $entries = array_filter($entries, fn ($e) => $e->getId() !== $this->entry->getId());
                }

                if (!count($entries)) {
                    return '';
                }

                if (count($entries) > self::RELATED_LIMIT) {
                    shuffle($entries); // randomize the order
                    $entries = array_slice($entries, 0, self::RELATED_LIMIT);
                }

                return $this->twig->render('entry/_related_sidebar.html.twig', ['entries' => $entries]);
            }
        );
    }
}
