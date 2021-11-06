<?php declare(strict_types=1);

namespace App\Twig;

use App\Twig\Runtime\FormattingRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class FormattingExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [FormattingRuntime::class, 'convertToHtml']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_short_desc', [FormattingRuntime::class, 'getShortDesc']),
        ];
    }
}
