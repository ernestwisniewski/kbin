<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\EmailExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EmailExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_css_source', [EmailExtensionRuntime::class, 'getEncoreEntryCssSource']),
        ];
    }
}
