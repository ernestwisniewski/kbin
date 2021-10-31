<?php declare(strict_types = 1);

namespace App\Twig;

use App\Twig\Runtime\FormattingRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class FormattingExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [FormattingRuntime::class, 'convertToHtml']),
        ];
    }
}
