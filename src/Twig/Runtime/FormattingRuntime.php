<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use App\Markdown\MarkdownConverter;

class FormattingRuntime implements RuntimeExtensionInterface
{
    public function __construct(private MarkdownConverter $markdownConverter)
    {
    }

    public function convertToHtml(string $value): string
    {
        return $this->markdownConverter->convertToHtml($value);
    }
}
