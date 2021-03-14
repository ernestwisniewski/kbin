<?php

namespace App\Twig;

use App\Markdown\MarkdownConverter;
use App\Twig\Runtime\FormattingRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class FormattingExtension extends AbstractExtension
{
    private MarkdownConverter $markdownConverter;

    public function __construct(MarkdownConverter $markdownConverter)
    {
        $this->markdownConverter = $markdownConverter;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [FormattingRuntime::class, 'convertToHtml']),
        ];
    }
}
