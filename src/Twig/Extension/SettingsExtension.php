<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\SettingsExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SettingsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('kbin_domain', [SettingsExtensionRuntime::class, 'kbinDomain']),
            new TwigFunction('kbin_title', [SettingsExtensionRuntime::class, 'kbinTitle']),
            new TwigFunction('kbin_meta_title', [SettingsExtensionRuntime::class, 'kbinMetaTitle']),
            new TwigFunction('kbin_meta_description', [SettingsExtensionRuntime::class, 'kbinDescription']),
            new TwigFunction('kbin_meta_keywords', [SettingsExtensionRuntime::class, 'kbinKeywords']),
            new TwigFunction('kbin_default_lang', [SettingsExtensionRuntime::class, 'kbinDefaultLang']),
            new TwigFunction('kbin_registrations_enabled', [SettingsExtensionRuntime::class, 'kbinRegistrationsEnabled']),
            new TwigFunction('kbin_header_logo', [SettingsExtensionRuntime::class, 'kbinHeaderLogo']),
            new TwigFunction('kbin_captcha_enabled', [SettingsExtensionRuntime::class, 'kbinCaptchaEnabled']),
            new TwigFunction('kbin_mercure_enabled', [SettingsExtensionRuntime::class, 'kbinMercureEnabled']),
            new TwigFunction('kbin_federation_page_enabled', [SettingsExtensionRuntime::class, 'kbinFederationPageEnabled']),
        ];
    }
}
