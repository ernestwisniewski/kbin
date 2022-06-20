<?php declare(strict_types=1);

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
            new TwigFunction('kbin_title', [SettingsRuntime::class, 'kbinTitle']),
            new TwigFunction('kbin_description', [SettingsRuntime::class, 'kbinDescription']),
            new TwigFunction('kbin_keywords', [SettingsRuntime::class, 'kbinKeywords']),
        ];
    }
}
