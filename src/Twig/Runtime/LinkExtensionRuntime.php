<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\Contracts\ContentInterface;
use App\Kbin\Factory\HtmlClassFactory;
use App\Service\SettingsManager;
use Twig\Extension\RuntimeExtensionInterface;

class LinkExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly HtmlClassFactory $generateHtmlClassService
    ) {
    }

    public function getRel(string $url): string
    {
        if (null === parse_url($url, PHP_URL_HOST) || $this->settingsManager->get('KBIN_DOMAIN') === parse_url($url, PHP_URL_HOST)) {
            return 'follow';
        }

        return 'nofollow noopener noreferrer';
    }

    public function getHtmlClass(ContentInterface $content): string
    {
        $service = $this->generateHtmlClassService;

        return $service->fromEntity($content);
    }

    public function getLinkDomain(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);

        if (null === $domain) {
            return $this->settingsManager->get('KBIN_DOMAIN');
        }

        return $domain;
    }
}
