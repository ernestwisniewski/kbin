<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Twig\Environment;

#[AsTwigComponent('featured_magazines')]
class FeaturedMagazinesComponent
{
    public ?Magazine $magazine;
    public bool $topbar = false;

    public function __construct(
        private readonly MagazineRepository $repository,
        private readonly Environment $twig,
        private readonly CacheInterface $cache,
        private readonly KernelInterface $kernel
    ) {
    }

    public function getHtml(): string
    {
        $env = $this->kernel->getEnvironment(); // @todo
        $magazines = $this->cache->get('featured_magazines', function (ItemInterface $item) use ($env) {
            $item->expiresAfter('test' === $env ? 0 : 60);

            $magazines = $this->repository->findBy(
                ['apId' => null, 'visibility' => VisibilityInterface::VISIBILITY_VISIBLE],
                ['lastActive' => 'DESC'],
                28
            );

            if ($this->magazine && !in_array($this->magazine, $magazines)) {
                array_unshift($magazines, $this->magazine);
            }

            usort($magazines, fn ($a, $b) => $a->lastActive < $b->lastActive);

            return array_map(fn ($mag) => $mag->name, $magazines);
        });

        return $this->twig->render(
            'magazine/_featured.html.twig',
            [
                'magazine' => $this->magazine,
                'magazines' => $magazines,
                'topbar' => $this->topbar,
            ]
        );
    }
}
