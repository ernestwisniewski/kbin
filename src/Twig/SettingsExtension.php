<?php

declare(strict_types=1);

namespace App\Twig;

use App\Twig\Runtime\SettingsRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SettingsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('kbin_domain', [SettingsRuntime::class, 'kbinDomain']),
            new TwigFunction('kbin_meta_title', [SettingsRuntime::class, 'kbinTitle']),
            new TwigFunction('kbin_meta_description', [SettingsRuntime::class, 'kbinDescription']),
            new TwigFunction('kbin_meta_keywords', [SettingsRuntime::class, 'kbinKeywords']),
            new TwigFunction('kbin_markdown_howto_url', [SettingsRuntime::class, 'kbinMarkdownHowtoUrl']),
            new TwigFunction('kbin_js_enabled', [SettingsRuntime::class, 'kbinJsEnabled']),
            new TwigFunction('kbin_registrations_enabled', [SettingsRuntime::class, 'kbinregistrationsEnabled']),
        ];
    }
}
