<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\VotableInterface;
use App\Repository\PartnerBlockRepository;
use App\Service\CacheService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;

#[AsTwigComponent('support_us_block', template: 'components/_cached.html.twig')]
final class SupportUsBlock
{
    public VotableInterface $subject;
    public string $url;

    public function __construct(
        private readonly Environment $twig,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack,
        private readonly PartnerBlockRepository $partnerBlockRepository
    ) {
    }

    public function getHtml(ComponentAttributes $attributes): string
    {
        return $this->cache->get(
            "support_us_block_{$this->requestStack->getCurrentRequest()?->getLocale()}",
            function (ItemInterface $item) {
                $item->expiresAfter(300);
                $partner = $this->partnerBlockRepository->findToDisplay();
                if (null === $partner) {
                    return '';
                }

                $partner->lastActive = new \DateTime();
                $this->partnerBlockRepository->save($partner);

                return $this->twig->render(
                    'layout/_support_us_block.html.twig',
                    [
                        'partner' => $this->partnerBlockRepository->findToDisplay(),
                    ]
                );
            }
        );
    }
}
