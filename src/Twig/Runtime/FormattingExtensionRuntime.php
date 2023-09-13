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

    /**
     * Generates a short sentence from a given input string.
     *
     * @param string|null $val       original string
     * @param int         $length    length string will be trimmed to
     * @param bool        $striptags optionally strips tags on string input
     * @param bool        $multiLine explodes the string into an array, generating multi-line content
     */
    public function getShortSentence(?string $val, $length = 50, $striptags = false, $multiLine = true): string
    {
        if (!$val) {
            return '';
        }

        $body = $striptags ? strip_tags(html_entity_decode($val)) : $val;

        if ($multiLine) {
            $body = wordwrap(trim($body), $length);
            $body = explode("\n", $body);
            $body = trim($body[0]).(isset($body[1]) ? '...' : '');
        } else {
            $body = trim($body);
            $body = substr($body, 0, $length);
            $body = ($body >= $length) ? $body .= '...' : $body;
        }

        return $body;
    }
}
