<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Entry;
use App\Repository\EntryRepository;
use App\Service\CacheService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('entries_cross', template: 'components/_cached.html.twig')]
final class EntriesCrossComponent
{
    public ?Entry $entry = null;

    public function __construct(
        private readonly EntryRepository $repository,
        private readonly CacheInterface $cache,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly Security $security
    ) {
    }

    public function getHtml(): string
    {
        return $this->cache->get(
            "entries_cross_{$this->entry->getId()}_{$this->security->getUser()?->getId()}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) {
                $item->expiresAfter(60);
                $entries = $this->repository->findCross($this->entry);

                $item->tag('entry_'.$this->entry->getId());
                foreach ($entries as $entry) {
                    $item->tag('entry_'.$entry->getId());
                }

                return $this->twig->render(
                    'components/entries_cross.html.twig',
                    [
                        'entries' => $this->repository->findCross($this->entry),
                    ]
                );
            }
        );
    }
}
