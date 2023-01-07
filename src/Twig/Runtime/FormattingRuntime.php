<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Markdown\MarkdownConverter;
use Twig\Extension\RuntimeExtensionInterface;

class FormattingRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly MarkdownConverter $markdownConverter)
    {
    }

    public function convertToHtml(?string $value): string
    {
        return $value ? $this->markdownConverter->convertToHtml($value) : '';
    }

    public function getShortDesc(?string $val): string
    {
        $body = wordwrap($val, 330);
        $body = explode("\n", $body);

        return trim($body[0]).(isset($body[1]) ? '...' : '');
    }
}
