<?php

namespace App\Twig\Runtime;

use App\Entity\Contracts\ContentInterface;
use App\Service\GenerateHtmlClassService;
use App\Service\SettingsManager;
use Twig\Extension\RuntimeExtensionInterface;

class LinkExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly GenerateHtmlClassService $generateHtmlClassService
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

        return $service($content);
    }
}
