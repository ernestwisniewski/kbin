<?php

namespace App\Twig;

use App\Markdown\MarkdownConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class FormattingExtension extends AbstractExtension
{
    /**
     * @var MarkdownConverter
     */
    private $markdownConverter;


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

    public static function highlightSearch(string $html): string
    {
        return preg_replace('!&lt;b&gt;(.*?)&lt;/b&gt;!', '<mark>\1</mark>', $html);
    }
}
