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
    public function kbinRegistrationsEnabled(): bool
    {
        return $this->settings->get('KBIN_REGISTRATIONS_ENABLED');
    }

    public function kbinDefaultLang(): string
    {
        return $this->settings->get('KBIN_DEFAULT_LANG');
    }

    public function kbinHeaderLogo(): bool
    {
        return $this->settings->get('KBIN_HEADER_LOGO');
    }

    public function kbinCaptchaEnabled(): bool
    {
        return $this->settings->get('KBIN_CAPTCHA_ENABLED');
    }

    public function kbinMercureEnabled(): bool
    {
        return $this->settings->get('KBIN_MERCURE_ENABLED');
    }

    public function kbinFederationPageEnabled(): bool
    {
        return $this->settings->get('KBIN_FEDERATION_PAGE_ENABLED');
    }

    public function kbinFederatedSearchOnlyLoggedIn(): bool
    {
        return $this->settings->get('KBIN_FEDERATED_SEARCH_ONLY_LOGGEDIN');
    }
}
