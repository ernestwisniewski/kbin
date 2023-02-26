<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Service\SettingsManager;
use JetBrains\PhpStorm\Pure;
use Twig\Extension\RuntimeExtensionInterface;

class SettingsExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly SettingsManager $settings)
    {
    }

    #[Pure]
    public function kbinDomain(): string
    {
        return $this->settings->get('KBIN_DOMAIN');
    }

    public function kbinTitle(): string
    {
        return $this->settings->get('KBIN_TITLE');
    }

    #[Pure]
    public function kbinMetaTitle(): string
    {
        return $this->settings->get('KBIN_META_TITLE');
    }

    #[Pure]
    public function kbinDescription(): string
    {
        return $this->settings->get('KBIN_META_DESCRIPTION');
    }

    #[Pure]
    public function kbinKeywords(): string
    {
        return $this->settings->get('KBIN_META_KEYWORDS');
    }

    #[Pure]
    public function kbinMarkdownHowtoUrl(): string
    {
        return $this->settings->get('KBIN_MARKDOWN_HOWTO_URL');
    }

    #[Pure]
    public function kbinJsEnabled(): bool
    {
        return $this->settings->get('KBIN_JS_ENABLED');
    }

    #[Pure]
    public function kbinRegistrationsEnabled(): bool
    {
        return $this->settings->get('KBIN_REGISTRATIONS_ENABLED');
    }
}
