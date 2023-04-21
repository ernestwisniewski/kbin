<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Markdown\MarkdownConverter;
use Twig\Extension\RuntimeExtensionInterface;

class FormattingExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly MarkdownConverter $markdownConverter)
    {
    }

    public function convertToHtml(?string $value): string
    {
        return $value ? $this->markdownConverter->convertToHtml($value) : '';
    }

    public function getShortSentence(?string $val, $length = 330): string
    {
        $body = wordwrap($val, $length);
        $body = explode("\n", $body);

        return trim($body[0]).(isset($body[1]) ? '...' : '');
    }
}
