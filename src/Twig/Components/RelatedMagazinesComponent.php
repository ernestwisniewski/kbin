<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('related_magazines', template: 'components/_cached.html.twig')]
final class RelatedMagazinesComponent
{
    public const TYPE_TAG = 'tag';
    public const TYPE_MAGAZINE = 'magazine';
    public const TYPE_RANDOM = 'random';

    public int $limit = 4;
    public ?string $tag = null;
    public ?string $magazine = null;
    public ?string $type = self::TYPE_RANDOM;
    public string $title = 'random_magazines';

    public function __construct(
        private readonly MagazineRepository $repository,
        private readonly CacheInterface $cache,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack
    ) {
    }

    #[PostMount]
    public function postMount(array $attr): array
    {
        if ($this->tag) {
            $this->title = 'related_magazines';
            $this->type = self::TYPE_TAG;
        }

        if ($this->magazine) {
            $this->title = 'related_magazines';
            $this->type = self::TYPE_MAGAZINE;
        }

        return $attr;
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        $magazine = str_replace('@', '', $this->magazine ?? '');

        return $this->cache->get(
            "related_magazines_{$magazine}_{$this->tag}_{$this->type}_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) use ($attributes, $magazine) {
                $item->expiresAfter(60);

                $magazines = match ($this->type) {
                    self::TYPE_TAG => $this->repository->findRelated($this->tag),
                    self::TYPE_MAGAZINE => $this->repository->findRelated($magazine),
                    default => $this->repository->findRandom(),
                };

                $magazines = array_filter($magazines, fn ($m) => $m->name !== $this->magazine);

                return $this->twig->render(
                    'components/related_magazines.html.twig',
                    [
                        'attributes' => $attributes,
                        'magazines' => $magazines,
                        'title' => $this->title,
                    ]
                );
            }
        );
    }
}
