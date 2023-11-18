<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('featured_magazines', template: 'components/_cached.html.twig')]
final class FeaturedMagazinesComponent
{
    public ?Magazine $magazine = null;

    public function __construct(private readonly Environment $twig, private readonly MagazineRepository $repository)
    {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        return $this->render();
    }

    private function render(): string
    {
        $magazines = $this->repository->findBy(
            ['apId' => null, 'visibility' => VisibilityInterface::VISIBILITY_VISIBLE],
            ['lastActive' => 'DESC'],
            28
        );

        if ($this->magazine && !\in_array($this->magazine, $magazines)) {
            array_unshift($magazines, $this->magazine);
        }

        usort($magazines, fn ($a, $b) => $a->lastActive < $b->lastActive ? 1 : -1);

        return $this->twig->render(
            'components/featured_magazines.html.twig',
            [
                'magazines' => array_map(fn ($mag) => $mag->name, $magazines),
                'magazine' => $this->magazine,
            ]
        );
    }
}
