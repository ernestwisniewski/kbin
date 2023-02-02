<?php

declare(strict_types=1);

namespace App\Twig;

use App\Twig\Runtime\SettingsRuntime;
use App\Twig\Runtime\UrlRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UrlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('entry_url', [UrlRuntime::class, 'entryUrl']),
        ];
    }
}
