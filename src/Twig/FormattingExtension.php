<?php

namespace App\Twig;

use App\Markdown\MarkdownConverter;
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
            new TwigFilter('markdown', [$this->markdownConverter, 'convertToHtml']),
        ];
    }
}
