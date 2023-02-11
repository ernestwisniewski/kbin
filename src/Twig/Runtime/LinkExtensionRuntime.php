<?php

namespace App\Twig\Runtime;

use App\Service\SettingsManager;
use Twig\Extension\RuntimeExtensionInterface;

class LinkExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly SettingsManager $settingsManager)
    {
    }

    public function getRel(string $url): string
    {
        if ($this->settingsManager->get('KBIN_DOMAIN') === parse_url($url, PHP_URL_HOST)) {
            return 'follow';
        };

        return 'nofollow noopener noreferrer';
    }
}